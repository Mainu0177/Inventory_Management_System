<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanProduct;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /**
     * Display the main Deliveries Page.
     */
    public function DeliveriesPage(Request $request)
    {
        $user_id = $request->header('id');

        $user = User::find($user_id);

        $settings = [
            'name'    => $user?->name    ?? 'Factory Electric Solution',
            'email'   => $user?->email   ?? '',
            'contact' => $user?->phone   ?? '',
            'address' => 'Dhaka, Bangladesh',
            'taxId'   => '',
            'logoUrl' => null,
        ];

        $customers = Customer::where('user_id', $user_id)->get();

        $quotations = Quotation::where('user_id', $user_id)
            ->with(['customer', 'quotationProducts.product'])
            ->orderBy('id', 'desc')
            ->get();

        $deliveryChallans = DeliveryChallan::where('user_id', $user_id)
            ->with(['customer', 'deliveryChallanProducts.product'])
            ->orderBy('id', 'desc')
            ->get();

        return Inertia::render('DeliveriesPage', [
            'settings'  => $settings,
            'customers' => $customers,
            'quotations' => $quotations,
            'challans'  => $deliveryChallans,
        ]);
    }

    /**
     * Create Delivery Challan from Quotation or Manual.
     */
    public function CreateDeliveryChallan(Request $request)
    {
        $user_id = $request->header('id');

        DB::beginTransaction();
        try {
            $challanNo = 'DC-' . date('Y') . '-' . substr(time(), -6);

            // 1. If creating from a quotation
            if ($request->has('quotation_id') && $request->input('quotation_id')) {
                $quotation = Quotation::where('user_id', $user_id)
                    ->with('quotationProducts')
                    ->findOrFail($request->input('quotation_id'));

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

            } else {
                // 2. Manual creation
                $request->validate([
                    'customer_id' => 'required|exists:customers,id',
                    'items' => 'required|array|min:1',
                    'items.*.name' => 'required|string',
                    'items.*.qty' => 'required|numeric|min:1',
                ]);

                $challan = DeliveryChallan::create([
                    'user_id' => $user_id,
                    'customer_id' => $request->input('customer_id'),
                    'challan_no' => $challanNo,
                    'pr_no' => $request->input('pr_no', ''),
                    'quotation_no' => 'MANUAL',
                    'po_no' => $request->input('po_no', ''),
                    'status' => 'Pending',
                    'created_by' => $request->input('created_by', 'Admin'),
                ]);

                foreach ($request->input('items') as $item) {
                    DeliveryChallanProduct::create([
                        'delivery_challan_id' => $challan->id,
                        'product_id' => null,
                        'code' => $item['code'] ?? null,
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'uom' => $item['uom'] ?? 'pcs',
                        'qty' => $item['qty'],
                        'unit_price' => 0,
                        'margin' => 0,
                        'discount' => 0,
                        'total_price' => 0,
                    ]);
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'Success',
                    'message' => 'Delivery Challan created successfully!',
                ]);
            }
            return redirect()->back()->with([
                'status' => true,
                'message' => 'Delivery Challan created successfully!',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'Error',
                    'message' => $e->getMessage(),
                ], 400);
            }
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to create challan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update Manual Delivery Challan.
     */
    public function UpdateDeliveryChallan(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'id' => 'required|exists:delivery_challans,id',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $challan = DeliveryChallan::where('user_id', $user_id)->findOrFail($request->input('id'));
            
            if ($challan->status === 'Delivered') {
                throw new Exception("Cannot edit a delivered challan.");
            }

            $challan->update([
                'customer_id' => $request->input('customer_id'),
                'pr_no' => $request->input('pr_no', ''),
                'po_no' => $request->input('po_no', ''),
            ]);

            // Clear old items and insert new ones
            DeliveryChallanProduct::where('delivery_challan_id', $challan->id)->delete();

            foreach ($request->input('items') as $item) {
                DeliveryChallanProduct::create([
                    'delivery_challan_id' => $challan->id,
                    'product_id' => $item['product_id'] ?? null,
                    'code' => $item['code'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'uom' => $item['uom'] ?? 'pcs',
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'] ?? 0,
                    'margin' => $item['margin'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'total_price' => $item['total_price'] ?? 0,
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['status' => 'Success', 'message' => 'Delivery Challan updated successfully!']);
            }
            return redirect()->back()->with(['status' => true, 'message' => 'Delivery Challan updated successfully!']);

        } catch (Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['status' => 'Error', 'message' => $e->getMessage()], 400);
            }
            return redirect()->back()->with(['status' => false, 'message' => 'Failed to update challan: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete Delivery Challan
     */
    public function DeleteDeliveryChallan(Request $request, $id)
    {
        $user_id = $request->header('id');
        try {
            $challan = DeliveryChallan::where('user_id', $user_id)->findOrFail($id);
            
            if ($challan->status === 'Delivered') {
                throw new Exception('Cannot delete a delivered challan.');
            }

            $challan->delete(); // automatically cascades deletes

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'Success', 'message' => 'Challan deleted successfully!']);
            }
            return redirect()->back()->with(['status' => true, 'message' => 'Challan deleted successfully!']);

        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'Error', 'message' => 'Failed to delete challan: ' . $e->getMessage()], 400);
            }
            return redirect()->back()->with(['status' => false, 'message' => 'Failed to delete challan: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark Delivery Challan as Delivered & Deduct Stock.
     */
    public function MarkChallanDelivered(Request $request)
    {
        $user_id = $request->header('id');
        $request->validate([
            'id' => 'required|exists:delivery_challans,id',
        ]);

        DB::beginTransaction();
        try {
            $challan = DeliveryChallan::where('user_id', $user_id)
                ->with('deliveryChallanProducts')
                ->findOrFail($request->input('id'));

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

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'Success',
                    'message' => 'Challan marked as Delivered and stock levels updated!',
                ]);
            }
            return redirect()->back()->with([
                'status' => true,
                'message' => 'Challan marked as Delivered and stock levels updated!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'Error',
                    'message' => $e->getMessage(),
                ], 400);
            }
            return redirect()->back()->with([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
