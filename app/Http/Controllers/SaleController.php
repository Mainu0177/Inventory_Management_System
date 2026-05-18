<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanProduct;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display the main Quotations Page.
     */
    public function SalePage(Request $request)
    {
        $user_id = $request->header('id');

        $customers = Customer::where('user_id', $user_id)->get();
        $products = Product::where('user_id', $user_id)->get();
        
        $quotations = Quotation::where('user_id', $user_id)
            ->with(['customer', 'quotationProducts.product'])
            ->orderBy('id', 'desc')
            ->get();

        $deliveryChallans = DeliveryChallan::where('user_id', $user_id)
            ->with(['customer', 'deliveryChallanProducts.product'])
            ->orderBy('id', 'desc')
            ->get();

        return Inertia::render('SalePage', [
            'customers' => $customers,
            'products' => $products,
            'quotations' => $quotations,
            'deliveryChallans' => $deliveryChallans
        ]);
    }

    /**
     * Create a new Quotation/Proposal.
     */
    public function CreateQuotation(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'pr_no' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate Quotation Number
            $quotationNo = 'QTN-' . date('Y') . '-' . substr(time(), -6);

            $quotation = Quotation::create([
                'user_id' => $user_id,
                'customer_id' => $request->input('customer_id'),
                'quotation_no' => $quotationNo,
                'pr_no' => $request->input('pr_no'),
                'total_amount' => $request->input('total_amount', 0),
                'status' => $request->input('status', 'Draft'),
                'valid_until' => $request->input('valid_until'),
                'terms' => $request->input('terms'),
                'created_by' => $request->input('created_by', 'Admin'),
            ]);

            $items = $request->input('items');
            $totalAmount = 0;

            foreach ($items as $item) {
                $productId = null;
                if (isset($item['product_id']) && $item['product_id'] !== 'manual') {
                    $productId = $item['product_id'];
                }

                $qty = (int) $item['qty'];
                $unitPrice = (float) $item['unit_price'];
                $margin = (int) ($item['margin'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                // calculate total price
                $priceWithMargin = $unitPrice * (1 + $margin / 100);
                $priceWithDiscount = $priceWithMargin * (1 - $discount / 100);
                $totalPrice = $priceWithDiscount * $qty;

                $totalAmount += $totalPrice;

                QuotationProduct::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $productId,
                    'code' => $item['code'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'uom' => $item['uom'] ?? 'pcs',
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'margin' => $margin,
                    'discount' => $discount,
                    'total_price' => $totalPrice,
                ]);
            }

            // Sync final verified total amount
            $quotation->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->back()->with([
                'status' => true,
                'message' => 'Quotation generated successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to create quotation: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update an existing Quotation.
     */
    public function UpdateQuotation(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'id' => 'required|exists:quotations,id',
            'customer_id' => 'required|exists:customers,id',
            'pr_no' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $quotation = Quotation::where('user_id', $user_id)->findOrFail($request->input('id'));

            $quotation->update([
                'customer_id' => $request->input('customer_id'),
                'pr_no' => $request->input('pr_no'),
                'status' => $request->input('status', $quotation->status),
                'valid_until' => $request->input('valid_until'),
                'terms' => $request->input('terms'),
            ]);

            // Clear old items and write updated ones
            QuotationProduct::where('quotation_id', $quotation->id)->delete();

            $items = $request->input('items');
            $totalAmount = 0;

            foreach ($items as $item) {
                $productId = null;
                if (isset($item['product_id']) && $item['product_id'] !== 'manual') {
                    $productId = $item['product_id'];
                }

                $qty = (int) $item['qty'];
                $unitPrice = (float) $item['unit_price'];
                $margin = (int) ($item['margin'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                $priceWithMargin = $unitPrice * (1 + $margin / 100);
                $priceWithDiscount = $priceWithMargin * (1 - $discount / 100);
                $totalPrice = $priceWithDiscount * $qty;

                $totalAmount += $totalPrice;

                QuotationProduct::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $productId,
                    'code' => $item['code'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'uom' => $item['uom'] ?? 'pcs',
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'margin' => $margin,
                    'discount' => $discount,
                    'total_price' => $totalPrice,
                ]);
            }

            $quotation->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->back()->with([
                'status' => true,
                'message' => 'Quotation updated successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to update quotation: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a Quotation.
     */
    public function DeleteQuotation(Request $request, $id)
    {
        $user_id = $request->header('id');
        try {
            $quotation = Quotation::where('user_id', $user_id)->findOrFail($id);
            $quotation->delete(); // Automatically cascade deletes QuotationProducts

            return redirect()->back()->with([
                'status' => true,
                'message' => 'Quotation deleted successfully',
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to delete quotation: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update quotation status.
     */
    public function UpdateQuotationStatus(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'id' => 'required|exists:quotations,id',
            'status' => 'required|string',
        ]);

        try {
            $quotation = Quotation::where('user_id', $user_id)->findOrFail($request->input('id'));
            $quotation->update([
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'status' => 'Success',
                'message' => 'Status updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Convert Quotation to standard invoice.
     */
    public function ConvertToInvoice(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
        ]);

        DB::beginTransaction();
        try {
            $quotation = Quotation::where('user_id', $user_id)
                ->with('quotationProducts')
                ->findOrFail($request->input('quotation_id'));

            // Create Invoice
            $invoiceNo = 'INV-' . date('Y') . '-' . substr(time(), -6);
            $invoice = Invoice::create([
                'user_id' => $user_id,
                'customer_id' => $quotation->customer_id,
                'invoice_no' => $invoiceNo,
                'quotation_no' => $quotation->quotation_no,
                'total' => $quotation->total_amount,
                'discount' => '0',
                'vat' => '0',
                'poNumber' => '',
                'prNumber' => $quotation->pr_no ?? '',
                'payable' => $quotation->total_amount,
                'status' => 'Unpaid',
                'paid_amount' => 0.00,
                'created_by' => $request->input('created_by', 'Admin'),
            ]);

            // Save products and deduct stock
            foreach ($quotation->quotationProducts as $qProd) {
                if ($qProd->product_id) {
                    $product = Product::lockForUpdate()->find($qProd->product_id);
                    if ($product) {
                        if ($product->unit < $qProd->qty) {
                            throw new Exception("Product '{$product->name}' has insufficient stock. Only {$product->unit} units left.");
                        }

                        // Deduct Stock
                        $product->decrement('unit', $qProd->qty);

                        // Log inventory movement
                        InventoryLog::create([
                            'user_id' => $user_id,
                            'product_id' => $product->id,
                            'type' => 'OUT',
                            'quantity' => $qProd->qty,
                            'reason' => "Converted from Quote #{$quotation->quotation_no}",
                            'reference_id' => $invoiceNo,
                        ]);
                    }
                }

                InvoiceProduct::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $qProd->product_id,
                    'code' => $qProd->code,
                    'name' => $qProd->name,
                    'description' => $qProd->description,
                    'uom' => $qProd->uom,
                    'qty' => $qProd->qty,
                    'sale_price' => $qProd->unit_price,
                    'margin' => $qProd->margin,
                    'discount' => $qProd->discount,
                    'total_price' => $qProd->total_price,
                    'user_id' => $user_id,
                ]);
            }

            // Update status
            $quotation->update(['status' => 'Invoiced']);

            DB::commit();
            return response()->json([
                'status' => 'Success',
                'message' => 'Invoice created successfully!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create Delivery Challan from Quotation.
     */
    public function CreateDeliveryChallan(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
        ]);

        DB::beginTransaction();
        try {
            $quotation = Quotation::where('user_id', $user_id)
                ->with('quotationProducts')
                ->findOrFail($request->input('quotation_id'));

            $challanNo = 'DC-' . date('Y') . '-' . substr(time(), -6);

            $challan = DeliveryChallan::create([
                'user_id' => $user_id,
                'customer_id' => $quotation->customer_id,
                'challan_no' => $challanNo,
                'pr_no' => $quotation->pr_no,
                'quotation_no' => $quotation->quotation_no,
                'po_no' => '',
                'status' => 'Pending',
                'created_by' => $request->input('created_by', 'Admin'),
            ]);

            foreach ($quotation->quotationProducts as $qProd) {
                DeliveryChallanProduct::create([
                    'delivery_challan_id' => $challan->id,
                    'product_id' => $qProd->product_id,
                    'code' => $qProd->code,
                    'name' => $qProd->name,
                    'description' => $qProd->description,
                    'uom' => $qProd->uom,
                    'qty' => $qProd->qty,
                    'unit_price' => $qProd->unit_price,
                    'margin' => $qProd->margin,
                    'discount' => $qProd->discount,
                    'total_price' => $qProd->total_price,
                ]);
            }

            // Mark Quote as Final
            $quotation->update(['status' => 'Final']);

            DB::commit();
            return response()->json([
                'status' => 'Success',
                'message' => 'Delivery Challan created successfully!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark Delivery Challan as Delivered & Deduct Stock.
     */
    public function MarkChallanDelivered(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'challan_id' => 'required|exists:delivery_challans,id',
        ]);

        DB::beginTransaction();
        try {
            $challan = DeliveryChallan::where('user_id', $user_id)
                ->with('deliveryChallanProducts')
                ->findOrFail($request->input('challan_id'));

            if ($challan->status === 'Delivered') {
                throw new Exception("Delivery Challan is already marked as Delivered.");
            }

            foreach ($challan->deliveryChallanProducts as $cProd) {
                if ($cProd->product_id) {
                    $product = Product::lockForUpdate()->find($cProd->product_id);
                    if ($product) {
                        if ($product->unit < $cProd->qty) {
                            throw new Exception("Product '{$product->name}' has insufficient stock. Only {$product->unit} units left.");
                        }

                        // Deduct Stock
                        $product->decrement('unit', $cProd->qty);

                        // Register Inventory Log
                        InventoryLog::create([
                            'user_id' => $user_id,
                            'product_id' => $product->id,
                            'type' => 'OUT',
                            'quantity' => $cProd->qty,
                            'reason' => "Delivered via Challan #{$challan->challan_no}",
                            'reference_id' => $challan->challan_no,
                        ]);
                    }
                }
            }

            $challan->update([
                'status' => 'Delivered',
                'delivered_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'status' => 'Success',
                'message' => 'Challan marked as Delivered and stock levels updated successfully!',
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
