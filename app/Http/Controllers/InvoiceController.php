<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function CreateInvoice(Request $request){
        DB::beginTransaction();
        try {
            $user_id = $request->header('id');
            $data = [
                'total' => $request->input('total'),
                'discount' => $request->input('discount'),
                'vat' => $request->input('vat'),
                'payable' => $request->input('payable'),
                'customer' => $request->input('customer_id'),
                'user_id' => $request->$user_id,
            ];
            $createInvoice = Invoice::create($data);

            return response()->json([
                'status' => ''
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
