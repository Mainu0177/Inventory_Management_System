<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function DashboardController(Request $request){
        $user_id = $request->header('id');

        $product = Product::where('user_id', $user_id)->count();
        $category = Category::where('user_id', $user_id)->count();
        $customer = Customer::where('user_id', $user_id)->count();
        $invoice = Invoice::where('user_id', $user_id)->count();
        $total = Invoice::where('user_id', $user_id)->sum('total');
        $vat = Invoice::where('user_id', $user_id)->sum('vat');
        $payable = Invoice::where('user_id', $user_id)->sum('payable');

        $data = [
            'Product' => $product,
            'Category' => $category,
            'Customer' => $customer,
            'Invoice' => $invoice,
            'Total' => $total,
            'Vat' => $vat,
            'Payable' => $payable
        ];
        return $data;
    }
}
