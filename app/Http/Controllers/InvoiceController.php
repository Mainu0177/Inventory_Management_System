<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanProduct;
use App\Models\InvoiceProduct;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display the Invoices page with all required props.
     */
    public function InvoiceListPage(Request $request)
    {
        $user_id = $request->header('id');

        // 1. Get all Invoices for this user with loaded customer and invoice products
        $list = Invoice::where('user_id', $user_id)
            ->with(['customer', 'invoiceProducts'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($inv) {
                // Map the DB structure to React-like structure for seamless integration
                return [
                    'id' => $inv->id,
                    'invoiceNo' => $inv->invoice_no ?? ('INV-' . $inv->id),
                    'quotationNo' => $inv->quotation_no,
                    'clientId' => $inv->customer_id,
                    'clientName' => $inv->customer ? $inv->customer->name : 'Unknown Client',
                    'poNo' => $inv->poNumber,
                    'prNo' => $inv->prNumber,
                    'totalAmount' => (float) $inv->total,
                    'paidAmount' => (float) $inv->paid_amount,
                    'status' => $inv->status,
                    'createdAt' => $inv->created_at,
                    'createdBy' => $inv->created_by,
                    'items' => $inv->invoiceProducts->map(function ($item) {
                        return [
                            'productId' => $item->product_id ? $item->product_id : 'manual',
                            'code' => $item->code,
                            'name' => $item->name,
                            'description' => $item->description,
                            'qty' => (int) $item->qty,
                            'uom' => $item->uom,
                            'unitPrice' => (float) $item->sale_price,
                            'margin' => (int) $item->margin,
                            'discount' => (float) $item->discount,
                            'totalPrice' => (float) $item->total_price,
                        ];
                    })->toArray(),
                ];
            });

        // 2. Fetch all customers, products, and approved quotations for creation/selection
        $customers = Customer::where('user_id', $user_id)->get();
        $products = Product::where('user_id', $user_id)->get();

        $quotations = Quotation::where('user_id', $user_id)
            ->whereIn('status', ['Approved', 'Sent'])
            ->with(['customer', 'quotationProducts'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'quotationNo' => $q->quotation_no,
                    'clientName' => $q->customer ? $q->customer->name : 'Unknown Client',
                    'totalAmount' => (float) $q->total_amount,
                    'status' => $q->status,
                    'prNo' => $q->pr_no,
                ];
            });

        return Inertia::render('InvoiceListPage', [
            'list' => $list,
            'customers' => $customers,
            'products' => $products,
            'quotations' => $quotations,
        ]);
    }

    /**
     * Create a new manual Invoice.
     */
    public function CreateInvoice(Request $request)
    {
        $user_id = $request->header('id');

        $request->validate([
            'clientId' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unitPrice' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate Invoice number
            $invoiceNo = $request->input('invoiceNo') ?? ('INV-' . date('Y') . '-' . substr(time(), -6));

            $items = $request->input('items');
            $totalAmount = 0;

            // Create Invoice record
            $invoice = Invoice::create([
                'user_id' => $user_id,
                'customer_id' => $request->input('clientId'),
                'invoice_no' => $invoiceNo,
                'quotation_no' => 'MANUAL',
                'total' => 0, // updated below
                'discount' => '0',
                'vat' => '0',
                'poNumber' => $request->input('poNo') ?? '',
                'prNumber' => $request->input('prNo') ?? '',
                'payable' => 0, // updated below
                'status' => $request->input('status', 'Unpaid'),
                'paid_amount' => $request->input('paidAmount', 0.00),
                'created_by' => $request->input('createdBy', 'Admin'),
            ]);

            // Save items and deduct stock
            foreach ($items as $item) {
                $productId = null;
                if (isset($item['productId']) && $item['productId'] !== 'manual') {
                    $productId = $item['productId'];
                }

                $qty = (int) $item['qty'];
                $unitPrice = (float) $item['unitPrice'];
                $margin = (int) ($item['margin'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                // Calculations
                $priceWithMargin = $unitPrice * (1 + $margin / 100);
                $priceWithDiscount = $priceWithMargin * (1 - $discount / 100);
                $totalPrice = $priceWithDiscount * $qty;
                $totalAmount += $totalPrice;

                // Deduct stock & log inventory
                if ($productId) {
                    $product = Product::lockForUpdate()->find($productId);
                    if ($product) {
                        if ($product->unit < $qty) {
                            throw new Exception("Product '{$product->name}' has insufficient stock. Only {$product->unit} units left.");
                        }
                        $product->decrement('unit', $qty);

                        InventoryLog::create([
                            'user_id' => $user_id,
                            'product_id' => $productId,
                            'type' => 'OUT',
                            'quantity' => $qty,
                            'reason' => "Manual Invoice #{$invoiceNo}",
                            'reference_id' => $invoiceNo,
                        ]);
                    }
                }

                InvoiceProduct::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $productId,
                    'code' => $item['code'] ?? 'MANUAL',
                    'name' => $item['name'],
                    'description' => $item['description'] ?? '',
                    'uom' => $item['uom'] ?? 'pcs',
                    'qty' => $qty,
                    'sale_price' => $unitPrice,
                    'margin' => $margin,
                    'discount' => $discount,
                    'total_price' => $totalPrice,
                    'user_id' => $user_id,
                ]);
            }

            // Update final total
            $invoice->update([
                'total' => $totalAmount,
                'payable' => $totalAmount,
            ]);

            DB::commit();
            return redirect('/InvoiceListPage')->with([
                'status' => true,
                'message' => 'Invoice created successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to create manual invoice: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update an existing manual Invoice.
     */
    public function UpdateInvoice(Request $request)
    {
        $user_id = $request->header('id');
        $invoiceId = $request->input('id');

        $request->validate([
            'id' => 'required|exists:invoices,id',
            'clientId' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::where('user_id', $user_id)->with('invoiceProducts')->findOrFail($invoiceId);

            // 1. Restore stock levels of previous items before updating
            foreach ($invoice->invoiceProducts as $prevProd) {
                if ($prevProd->product_id) {
                    $product = Product::find($prevProd->product_id);
                    if ($product) {
                        $product->increment('unit', $prevProd->qty);

                        // Delete inventory log for OUT movement of this invoice
                        InventoryLog::where('user_id', $user_id)
                            ->where('product_id', $prevProd->product_id)
                            ->where('reference_id', $invoice->invoice_no)
                            ->delete();
                    }
                }
            }

            // 2. Delete old products linked to this invoice
            InvoiceProduct::where('invoice_id', $invoice->id)->delete();

            // 3. Save new items, update totals and deduct stock
            $items = $request->input('items');
            $totalAmount = 0;

            foreach ($items as $item) {
                $productId = null;
                if (isset($item['productId']) && $item['productId'] !== 'manual') {
                    $productId = $item['productId'];
                }

                $qty = (int) $item['qty'];
                $unitPrice = (float) $item['unitPrice'];
                $margin = (int) ($item['margin'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                // Calculations
                $priceWithMargin = $unitPrice * (1 + $margin / 100);
                $priceWithDiscount = $priceWithMargin * (1 - $discount / 100);
                $totalPrice = $priceWithDiscount * $qty;
                $totalAmount += $totalPrice;

                // Deduct stock & log inventory
                if ($productId) {
                    $product = Product::lockForUpdate()->find($productId);
                    if ($product) {
                        if ($product->unit < $qty) {
                            throw new Exception("Product '{$product->name}' has insufficient stock. Only {$product->unit} units left.");
                        }
                        $product->decrement('unit', $qty);

                        InventoryLog::create([
                            'user_id' => $user_id,
                            'product_id' => $productId,
                            'type' => 'OUT',
                            'quantity' => $qty,
                            'reason' => "Manual Invoice #{$invoice->invoice_no} (Updated)",
                            'reference_id' => $invoice->invoice_no,
                        ]);
                    }
                }

                InvoiceProduct::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $productId,
                    'code' => $item['code'] ?? 'MANUAL',
                    'name' => $item['name'],
                    'description' => $item['description'] ?? '',
                    'uom' => $item['uom'] ?? 'pcs',
                    'qty' => $qty,
                    'sale_price' => $unitPrice,
                    'margin' => $margin,
                    'discount' => $discount,
                    'total_price' => $totalPrice,
                    'user_id' => $user_id,
                ]);
            }

            // 4. Update Invoice model totals and metadata
            $invoice->update([
                'customer_id' => $request->input('clientId'),
                'poNumber' => $request->input('poNo') ?? '',
                'prNumber' => $request->input('prNo') ?? '',
                'total' => $totalAmount,
                'payable' => $totalAmount,
            ]);

            DB::commit();
            return redirect('/InvoiceListPage')->with([
                'status' => true,
                'message' => 'Invoice updated successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to update invoice: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Toggle status or update invoice payment status.
     */
    public function UpdateInvoiceStatus(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'id' => 'required|exists:invoices,id',
            'status' => 'required|string|in:Paid,Unpaid,Partial',
        ]);

        try {
            $invoice = Invoice::where('user_id', $user_id)->findOrFail($request->input('id'));
            $newStatus = $request->input('status');
            $paidAmount = ($newStatus === 'Paid') ? (float) $invoice->total : 0.00;

            $invoice->update([
                'status' => $newStatus,
                'paid_amount' => $paidAmount,
            ]);

            return response()->json([
                'status' => 'Success',
                'message' => 'Invoice payment status updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Invoice and restore items inventory stock.
     */
    public function InvoiceDelete(Request $request, $id)
    {
        $user_id = $request->header('id');

        DB::beginTransaction();
        try {
            $invoice = Invoice::where('user_id', $user_id)->with('invoiceProducts')->findOrFail($id);

            // Restore stock of all invoice items
            foreach ($invoice->invoiceProducts as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('unit', $item->qty);
                    }
                }
            }

            // Remove inventory logs linked to this invoice
            InventoryLog::where('user_id', $user_id)
                ->where('reference_id', $invoice->invoice_no)
                ->delete();

            // Delete invoice products and invoice itself
            $invoice->invoiceProducts()->delete();
            $invoice->delete();

            DB::commit();
            return redirect('/InvoiceListPage')->with([
                'status' => true,
                'message' => 'Invoice deleted successfully and inventory stocks restored',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to delete invoice: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Create Delivery Challan from Invoice.
     */
    public function CreateDeliveryChallan(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::where('user_id', $user_id)
                ->with('invoiceProducts')
                ->findOrFail($request->input('invoice_id'));

            $challanNo = 'DC-' . date('Y') . '-' . substr(time(), -6);

            $challan = DeliveryChallan::create([
                'user_id' => $user_id,
                'customer_id' => $invoice->customer_id,
                'challan_no' => $challanNo,
                'pr_no' => $invoice->prNumber,
                'quotation_no' => $invoice->quotation_no ?? 'MANUAL',
                'po_no' => $invoice->poNumber ?? '',
                'status' => 'Pending',
                'created_by' => $request->input('created_by', 'Admin'),
            ]);

            foreach ($invoice->invoiceProducts as $invProd) {
                DeliveryChallanProduct::create([
                    'delivery_challan_id' => $challan->id,
                    'product_id' => $invProd->product_id,
                    'code' => $invProd->code,
                    'name' => $invProd->name,
                    'description' => $invProd->description,
                    'uom' => $invProd->uom,
                    'qty' => $invProd->qty,
                    'unit_price' => $invProd->sale_price,
                    'margin' => $invProd->margin,
                    'discount' => $invProd->discount,
                    'total_price' => $invProd->total_price,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'Success',
                'message' => 'Delivery Challan generated successfully from Invoice!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
