<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\InvoiceProduct;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function CreateInvoice(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            $user_id = $request->header('id');
            $data = [
                'total' => $request->input('total'),
                'discount' => $request->input('discount'),
                'vat' => $request->input('vat'),
                'payable' => $request->input('payable'),
                'customer_id' => $request->input('customer_id'),
                'user_id' => $user_id,
            ];
            $createInvoice = Invoice::create($data);

            $products = $request->input('products');
            foreach ($products as $product) {
                $existingProduct = Product::where('id', $product['id'])->select('unit')->first();
                if (!$existingProduct) {
                    // return response()->json([
                    //     'status' => 'Failed',
                    //     'message' => "Product with ID {$product['id']} not found",
                    // ], 404);
                    $data = [
                    'message' => 'Product with ID '.$product['id']. 'not found',
                    'status' => false,
                    'error' => '',
                ];
                    return redirect()->back()->with($data);
                }
                if ($existingProduct->unit < $product['unit']) {
                    // return response()->json([
                    //     'status' => 'Failed',
                    //     'message' => "Only {$existingProduct->unit} Units available for product with ID {$product['id']}"
                    // ]);
                    $data = [
                    'message' => 'Only '.$existingProduct->unit.' Units available for Product with ID '.$product['id'],
                    'status' => false,
                    'error' => '',
                ];
                    return redirect()->back()->with($data);
                }

                InvoiceProduct::create([
                    'invoice_id' => $createInvoice->id,
                    'product_id' => $product['id'],
                    'user_id' => $user_id,
                    'qty' => $product['unit'],
                    'sale_price' => $product['price'],
                ]);

                Product::where('id', $product['id'])->update([
                    'unit' => $existingProduct->unit - $product['unit'],
                ]);
            }

            DB::commit();
            // return response()->json([
            //     'status' => 'Success',
            //     'message' => 'Invoice Created Successfully',
            // ], 200);
            $data = [
                'message' => 'Invoice created Successfully',
                'status' => true,
                'error' => '',
            ];
            return redirect('/InvoiceListPage')->with($data);
        } catch (Exception $e) {
            DB::rollBack();
            $data = [
                'message' => 'Something went wrong, please try again later',
                'status' => false,
                'error' => '',
            ];
            return redirect('/InvoiceListPage')->with($data);
        }
    }// end method
    public function InvoiceList(Request $request)
    {
        try {
            $user_id = $request->header('id');
            return Invoice::where('user_id', $user_id)->with('customer')->get();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }// end method

    public function InvoiceDetails(Request $request)
    {
        $user_id = $request->header('id');
        $customerDetails = Customer::where('user_id', $user_id)->where('id', $request->input('customer_id'))->first();
        $invoiceDetails = Invoice::where('user_id', $user_id)->where('id', $request->input('invoice_id'))->first();
        $invoiceProduct = InvoiceProduct::where('user_id', $user_id)->where('invoice_id', $request->input('invoice_id'))->with('product')->get();

        return [
            'customer' => $customerDetails,
            'invoice' => $invoiceDetails,
            'product' => $invoiceProduct,
        ];
    }// end method

    public function InvoiceDelete(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->header('id');
            InvoiceProduct::where('user_id', $user_id)->where('invoice_id', $id)->delete();
            Invoice::where('user_id', $user_id)->where('id', $id)->delete();
            DB::commit();
            // return response()->json([
            //     'status' => 'Success',
            //     'message' => 'Invoice deleted Successfully'
            // ]);
            $data = [
                'status' => true,
                'message' => "Invoice deleted Successfully",
                'error' => ''
            ];
            return redirect('/InvoiceListPage')->with($data);
        } catch (Exception $e) {
            DB::rollBack();
            // return response()->json([
            //     'status' => 'Failed',
            //     // 'message' => "Invoice dose not deleted, Please try again later",
            //     'message' => $e->getMessage()
            // ]);
            $data = [
                'status' => false,
                'message' => "Invoice dose not deleted, Please try again letter",
                'error' => ''
            ];
            return redirect()->back()->with($data);
        }
    }


    public function InvoiceListPage(Request $request)
    {
        $user_id = $request->header('id');
        $list = Invoice::where('user_id', $user_id)->with(['customer', 'invoiceProducts.product'])->get();
        // return $list;
        return Inertia::render('InvoiceListPage', ['list' => $list]);

    }
}
