<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function CreateCustomer(Request $request)
    {
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

            // return response()->json([
            //     'status' => 'Success',
            //     'message' => "Customer created successfully",
            //     'data' => $createCustomer
            // ], 200);
            $data = [
                'message' => "Customer created successfully",
                'status' => true,
                'error' => ''
            ];
            return redirect('/CustomerPage')->with($data);
        } catch (\Exception $e) {
            // return response()->json([
            //     'status' => 'Failed',
            //     // 'message' => "Customer does not created, Please try again later",
            //     'message' => $e->getMessage()
            // ], 500);
            $data = [
                'message' => "Customer does not created, Please try again later",
                'status' => false,
                'error' => ''
            ];
            return redirect('/CustomerSavePage')->with($data);
        }
    }// end method

    public function CustomerList(Request $request)
    {
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

    public function CustomerDetail(Request $request)
    {
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

    public function CustomerUpdate(Request $request)
    {
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
            // return response()->json([
            //     'status' => 'Success',
            //     'message' => 'Customer updated successfully',
            //     'data' => $updateCustomer,
            // ], 200);
            $data = [
                'status' => true,
                'message' => "Customer updated successfully",
                'error' => ''
            ];
            return redirect('/CustomerPage')->with($data);
        } catch (\Exception $e) {
            // return response()->json([
            //     'status' => 'Failed',
            //     // 'message' => 'Customer does not updated, Please try again later',
            //     'message' => $e->getMessage(),
            // ], 500);
            $data = [
                'status' => false,
                'message' => "Customer does not updated, Please try again letter",
                'error' => ''
            ];
            return redirect('/CustomerSavePage')->with($data);
        }
    }// end method
    public function CustomerDelete(Request $request, $id)
    {
        try {
            $user_id = $request->header('id');
            Customer::where('id', $id)->where('user_id', $user_id)->delete();
            // $deleteCustomer = Customer::where('id', $request->input('id'))->where('user_id', $user_id)->delete();
            // return response()->json([
            //     'status' => 'Success',
            //     'message' => 'Customer deleted successfully',
            //     'data' => $deleteCustomer,
            // ], 200);

            $data = [
                'status' => true,
                'message' => "Customer deleted successfully",
                'error' => ''
            ];
            return redirect()->back()->with($data);
        } catch (\Exception $e) {
            // return response()->json([
            //     'status' => 'Failed',
            //     'message' => 'Customer does not deleted, Please try again later',
            //     // 'message' => $e->getMessage();
            // ], 500);
            $data = [
                'status' => false,
                'message' => "Customer does not deleted, Please try again letter",
                'error' => ''
            ];
            return redirect()->back()->with($data);
        }
    }


    public function CustomerPage(Request $request){
        $user_id = $request->header('id');
        $customers = Customer::where('user_id', $user_id)->latest()->get();
        return Inertia::render('CustomerPage', ['customers' => $customers]);
    }

    public function CustomerSavePage(Request $request){
        $user_id = $request->header('id');
        $id = $request->query('id');

        $customer = Customer::where('id', $id)->where('user_id', $user_id)->first();
        return Inertia::render('CustomerSavePage', ['customer' => $customer]);
    }

}
