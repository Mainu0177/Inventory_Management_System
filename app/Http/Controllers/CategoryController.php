<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function CreateCategory(Request $request) {
        // dd($request->all());
        try {
            $user_id = $request->header('id');
            Category::create([
            'name' => $request->name,
            'user_id' => $user_id,
        ]);
        return response()->json([
            'status' => "Success",
            'message' => "Category Created Successfully",
        ],200);
        } catch (Exception $e) {
            return response()->json([
                'status' => "Failed",
                'message' => "Something went wrong, Please try again later",
            ]);
        }
    } // end method

    public function ListCategory(Request $request){
        try {
            $user_id = $request->header('id');
            $category = Category::where('user_id', $user_id)->get();
            return response()->json([
                'status' => "Success",
                'category' => $category,
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'status' => "Failed",
                'message' => "Something went wrong, Please try again later",
            ]);
        }
    }// end method

    public function CategoryDetailsById(Request $request){
        try {
            $user_id = $request->header('id');
            // $category_id = $request->id;

            $category = Category::where('id', $request->input('id'))->where('user_id', $user_id)->firstOrFail();
            return response()->json([
                'status' => "Success",
                'message' => "Category Found Successfully",
                'data' => $category
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => "Failed",
                'message' => "Category not found",
            ]);
        }
    }
}
