<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function CreateCustomer(Request $request){
        try {
            $user_id = $request->header('id');

            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'mobile' => 'required',
            ]);
            $createCustomer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'user_id' => $user_id,
            ]);

            return response()->json([
                'status' => 'Success',
                'message' => "Customer created successfully",
                'data' => $createCustomer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Failed',
                // 'message' => "Customer does not created, Please try again later",
                'message' => $e->getMessage()
            ], 500);
        }
    }// end method

    public function CustomerList(Request $request){
        try {
            $user_id = $request->header('id');
            $allCustomer = Customer::where('user_id', $user_id)->get();
            return response()->json([
                'status' => 'Success',
                'message' => "All Customers found",
                'data' => $allCustomer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Failed',
                'message' => "Customer does not found"
            ]);
        }
    }// end method

    public function CustomerDetail(Request $request){
        try {
            $user_id = $request->header('id');
            $customerDetail = Customer::where('id', $request->input('id'))->where('user_id', $user_id)->firstOrFail();
            return response()->json([
                'status' => 'Success',
                'message' => 'Customer Details found',
                'data' => $customerDetail
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Failed',
                'message' => "Customer does not found"
                // 'message' => $e->getMessage()
            ]);
        }
    }// end method

    public function CustomerUpdate(Request $request){
        try {
            $user_id = $request->header('id');

            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'mobile' => 'required',
            ]);
            $updateCustomer = Customer::where('id', $request->input('id'))->where('user_id', $user_id)->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
            ]);
            return response()->json([
                'status' => 'Success',
                'message' => 'Customer updated successfully',
                'data' => $updateCustomer,
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Failed',
                // 'message' => 'Customer does not updated, Please try again later',
                'message' => $e->getMessage(),
            ],500);
        }
    }// end method
    public function CustomerDelete(Request $request){
        try {
            $user_id = $request->header('id');
            $deleteCustomer = Customer::where('id', $request->input('id'))->where('user_id', $user_id)->delete();
            return response()->json([
                'status' => 'Success',
                'message' => 'Customer deleted successfully',
                'data' => $deleteCustomer,
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Failed',
                'message' => 'Customer does not deleted, Please try again later',
                // 'message' => $e->getMessage();
            ],500);
        }
    }
}
