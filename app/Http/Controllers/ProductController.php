<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function CreateProduct(Request $request){
        try {
            $user_id = $request->header('id');

            $request->validate([
                'name' =>'required',
                'price' =>'required',
                'unit' =>'required',
                'category_id' =>'required',
                'image' =>'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            ]);

            $data = [
                'name' => $request->name,
                'price' => $request->price,
                'unit' => $request->unit,
                'category_id' => $request->category_id,
                'user_id' => $user_id,
            ];

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $filePath = 'uploads/' . $filename;
                $file->move(public_path('uploads'), $filename);
                $data['image'] = $filePath;
            }

            Product::create($data);

            return response()->json([
                'status' => "Success",
                'message' => "Product Created Successfully",
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'status' => "Failed",
                'message' => "Product not created, Please try again later",
            ]);
        }
    }
}
