<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // ─── CREATE PRODUCT ──────────────────────────────────────────────────────

    public function CreateProduct(Request $request)
    {
        try {
            $user_id = $request->header('id');

            $request->validate([
                'name'                => 'required|string|max:100',
                'code'                => 'required|string|max:50',
                'description'         => 'nullable|string|max:1000',
                'uom'                 => 'nullable|string|max:20',
                'price'               => 'required|numeric|min:0',
                'unit'                => 'required|integer|min:0', // initial stock
                'low_stock_threshold' => 'required|integer|min:0',
                'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            ]);

            $data = [
                'name'                => $request->name,
                'code'                => $request->code,
                'description'         => $request->description,
                'uom'                 => $request->uom ?: 'pcs',
                'price'               => $request->price,
                'unit'                => $request->unit,
                'low_stock_threshold' => $request->low_stock_threshold,
                'user_id'             => $user_id,
            ];

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = 'uploads/' . $filename;
                $file->move(public_path('uploads'), $filename);
                $data['image'] = $filePath;
            }

            DB::transaction(function () use ($data, $user_id) {
                $product = Product::create($data);

                // Create initial stock IN log if initial stock is > 0
                if ($product->unit > 0) {
                    InventoryLog::create([
                        'product_id'   => $product->id,
                        'user_id'      => $user_id,
                        'type'         => 'IN',
                        'quantity'     => $product->unit,
                        'reference_id' => 'INIT-' . str_pad($product->id, 4, '0', STR_PAD_LEFT),
                        'reason'       => 'Initial Stock Registration',
                    ]);
                }
            });

            return redirect('/ProductPage')->with([
                'message' => 'Product Created Successfully',
                'status'  => true,
                'error'   => ''
            ]);
        } catch (Exception $e) {
            return redirect('/ProductPage')->with([
                'message' => 'Product could not be created, please try again.',
                'status'  => false,
                'error'   => $e->getMessage()
            ]);
        }
    }

    // ─── LIST ────────────────────────────────────────────────────────────────

    public function ProductList(Request $request)
    {
        try {
            $user_id = $request->header('id');
            $allProduct = Product::where('user_id', $user_id)->orderBy('name', 'asc')->get();
            return response()->json([
                'status'  => 'Success',
                'message' => 'All Products found',
                'data'    => $allProduct
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'Failed',
                'message' => 'Products not found'
            ]);
        }
    }

    // ─── DETAIL ──────────────────────────────────────────────────────────────

    public function ProductDetail(Request $request)
    {
        try {
            $user_id = $request->header('id');
            $productDetail = Product::where('id', $request->input('id'))->where('user_id', $user_id)->firstOrFail();
            return response()->json([
                'status'  => 'Success',
                'message' => 'Product Details found',
                'data'    => $productDetail
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'Failed',
                'message' => $e->getMessage()
            ]);
        }
    }

    // ─── UPDATE PRODUCT ──────────────────────────────────────────────────────

    public function ProductUpdate(Request $request)
    {
        try {
            $user_id = $request->header('id');

            $request->validate([
                'id'                  => 'required|integer',
                'name'                => 'required|string|max:100',
                'code'                => 'required|string|max:50',
                'description'         => 'nullable|string|max:1000',
                'uom'                 => 'nullable|string|max:20',
                'price'               => 'required|numeric|min:0',
                'low_stock_threshold' => 'required|integer|min:0',
                'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
            ]);

            $product = Product::where('user_id', $user_id)->findOrFail($request->input('id'));
            
            $product->name                = $request->input('name');
            $product->code                = $request->input('code');
            $product->description         = $request->input('description');
            $product->uom                 = $request->input('uom') ?: 'pcs';
            $product->price               = $request->input('price');
            $product->low_stock_threshold = $request->input('low_stock_threshold');

            if ($request->hasFile('image')) {
                if ($product->image && file_exists(public_path($product->image))) {
                    @unlink(public_path($product->image));
                }
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = 'uploads/' . $filename;
                $file->move(public_path('uploads'), $filename);
                $product->image = $filePath;
            }

            $product->save();

            return redirect('/ProductPage')->with([
                'message' => 'Product Updated Successfully',
                'status'  => true,
                'error'   => ''
            ]);
        } catch (Exception $e) {
            return redirect('/ProductPage')->with([
                'message' => 'Product could not be updated, please try again.',
                'status'  => false,
                'error'   => $e->getMessage()
            ]);
        }
    }

    // ─── ADJUST STOCK ────────────────────────────────────────────────────────

    public function AdjustStock(Request $request)
    {
        try {
            $user_id = (int)$request->header('id');

            $request->validate([
                'product_id'   => 'required|integer',
                'type'         => 'required|in:IN,OUT',
                'quantity'     => 'required|integer|min:1',
                'reason'       => 'required|string|max:255',
                'reference_id' => 'nullable|string|max:100',
            ]);

            $productId = $request->input('product_id');
            $quantity  = $request->input('quantity');
            $type      = $request->input('type');
            $reason    = $request->input('reason');
            $refId     = $request->input('reference_id') ?: 'ADJ-' . strtoupper(uniqid());

            DB::transaction(function () use ($productId, $quantity, $type, $reason, $refId, $user_id) {
                $product = Product::where('id', $productId)->where('user_id', $user_id)->lockForUpdate()->firstOrFail();

                // Compute new stock level
                if ($type === 'IN') {
                    $product->unit = $product->unit + $quantity;
                } else {
                    $product->unit = max(0, $product->unit - $quantity);
                }
                $product->save();

                // Log adjustment transaction
                InventoryLog::create([
                    'product_id'   => $productId,
                    'user_id'      => $user_id,
                    'type'         => $type,
                    'quantity'     => $quantity,
                    'reference_id' => $refId,
                    'reason'       => $reason,
                ]);
            });

            return response()->json([
                'status'  => 'Success',
                'message' => 'Inventory stock adjusted successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'Failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ─── INVENTORY LOGS ──────────────────────────────────────────────────────

    public function InventoryLogs(Request $request)
    {
        try {
            $user_id = (int)$request->header('id');
            $productId = (int)$request->query('product_id');

            $logs = InventoryLog::where('user_id', $user_id)
                ->where('product_id', $productId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($log) => [
                    'id'           => $log->id,
                    'type'         => $log->type,
                    'quantity'     => $log->quantity,
                    'reference_id' => $log->reference_id ?: 'N/A',
                    'reason'       => $log->reason ?: 'No reason stated',
                    'date'         => $log->created_at->format('d M Y, h:i A'),
                ]);

            return response()->json([
                'status' => 'Success',
                'logs'   => $logs,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'Failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ─── DELETE PRODUCT ──────────────────────────────────────────────────────

    public function ProductDelete(Request $request, $id)
    {
        try {
            $user_id = $request->header('id');
            $product = Product::where('user_id', $user_id)->findOrFail($id);

            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }
            $product->delete();

            return redirect()->back()->with([
                'message' => 'Product deleted Successfully',
                'status'  => true,
                'error'   => ''
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'message' => 'Product could not be deleted.',
                'status'  => false,
                'error'   => $e->getMessage()
            ]);
        }
    }

    // ─── PAGES ───────────────────────────────────────────────────────────────

    public function ProductPage()
    {
        $user_id = request()->header('id');
        $products = Product::where('user_id', $user_id)->orderBy('name', 'asc')->get();
        return Inertia::render('ProductPage', ['products' => $products]);
    }

    public function ProductSavePage(Request $request)
    {
        $user_id = request()->header('id');
        $product = Product::where('id', $request->query('id'))->where('user_id', $user_id)->first();
        return Inertia::render('ProductSavePage', ['product' => $product]);
    }
}
