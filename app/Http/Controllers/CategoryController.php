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
                'message' => "Category dose not created, Please try again later",
            ], 500);
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
    }// end method

    public function UpdateCategory(Request $request){
        try {
            $user_id = $request->header('id');
            $updateCategory = Category::where('id', $request->input('id'))->where('user_id', $user_id)->update([
            'name' => $request->name
        ]);
            return response()->json([
                'status' => "Success",
                'message' => "Category Updated Successfully",
                'data' => $updateCategory
        ],200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => "Failed",
                    'message' => "Category not updated, Please try again later",
                ],500);
            }
    }

    // public function DeleteCategory(Request $request){
    //     try {
    //         $user_id = $request->header('id');
    //         $deleteCategory = Category::where('id', $request->input('id'))->where('user_id', $user_id)->delete();
    //         return response()->json([
    //             'status' => "Success",
    //             'message' => "Category Deleted Successfully",
    //             'data' => $deleteCategory
    //         ],200);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => "Failed",
    //             'message' => "Category dose not deleted, Please try again later",
    //         ],500);
    //     }
    // }// end method

    // alternative system for delete
    public function DeleteCategory(Request $request, $id){
        $user_id = $request->header('id');
        Category::where('id', $id)->where('user_id', $user_id)->delete();
        return response()->json([
            'status' => "Success",
            'message' => "Category Deleted Successfully",
        ],200);
    }// end method
}
