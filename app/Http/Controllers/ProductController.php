<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
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
                $filename = time() . '.' . $file->getClientOriginalName();
                $filePath = 'uploads/' . $filename;
                $file->move(public_path('uploads'), $filename);
                $data['image'] = $filePath;
            }
            // if ($request->hasFile('image')) {
            //     $file = $request->file('image');
            //     $filename = time() . '.' . $file->getClientOriginalExtension();
            //     $filePath = 'uploads/' . $filename;
            //     $file->move(public_path('uploads'), $filename);
            //     $data['image'] = $filePath;
            // }

            Product::create($data);

            // return response()->json([
            //     'status' => "Success",
            //     'message' => "Product Created Successfully",
            // ],200);
            $data = [
                'message' => "Product Created Successfully",
                'status' => true,
                'error' => ''
            ];
            return redirect('/ProductPage')->with($data);
        } catch (Exception $e) {
            // return response()->json([
            //     'status' => "Failed",
            //     // 'message' => "Product not created, Please try again later",
            //     'message' => $e->getMessage(),
            // ]);
            $data = [
                'message' => "Product not created, Please try again later",
                'status' => false,
                'error' => $e->getMessage()
            ];
            return redirect('/ProductSavePage')->with($data);
        }
    } // end method

    public function ProductList(Request $request){
        try {
            $user_id = $request->header('id');
            $allProduct = Product::where('user_id', $user_id)->get();
            // return response()->json([
            //     'status' => 'Success',
            //     'message' => "All Product found",
            //     'data' => $allProduct
            // ]);
        } catch (Exception $e) {
            // return response()->json([
            //     'status' => 'Failed',
            //     'message' => "Product does not found"
            // ]);
        }
    }// end method

    public function ProductDetail(Request $request){
        try {
            $user_id = $request->header('id');
            $productDetail = Product::where('id', $request->input('id'))->where('user_id', $user_id)->firstOrFail();
            return response()->json([
                'status' => 'Success',
                'message' => 'Product Details found',
                'data' => $productDetail
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Failed',
                // 'message' => "Product does not found"
                'message' => $e->getMessage()
            ]);
        }
    }// end method

    public function ProductUpdate(Request $request){
        try {
            // dd($request->all());
            $user_id = $request->header('id');
            $request->validate([
                'name' => 'required',
                'price' => 'required',
                'unit' => 'required',
                'category_id' => 'required'
            ]);

            $product = Product::where('user_id', $user_id)->findOrFail($request->input('id'));
            $product->name = $request->input('name');
            $product->price = $request->input('price');
            $product->unit = $request->input('unit');
            $product->category_id = $request->input('category_id');

            if($request->hasFile('image')){
                if($product->image && file_exists(public_path($product->image))){
                    unlink(public_path($product->image));
                }
                $request->validate([
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
                ]);
                $file = $request->file('image');
                $filename = time() . '.' . $file->getClientOriginalName();
                $filePath = 'uploads/' . $filename;
                $file->move(public_path('uploads'), $filename);
                $product->image = $filePath;
            }
            $product->save();
            // return response()->json([
            //     'status' => "Success",
            //     'message' => "Product Updated Successfully",
            //     'data' => $product
            // ],200);
            $data = [
                'message' => "Product Updated Successfully",
                'status' => true,
                'error' => ''
            ];
            return redirect('/ProductPage')->with($data);
        } catch (Exception $e) {
            // return response()->json([
            //     'status' => "Success",
            //     'message' => "Product dose not Updated, please try again later",
            //     // 'message' => $e->getMessage(),
            // ], 500);
            $data = [
                'message' => "Product dose not Updated, please try again later",
                'status' => false,
                'error' => ''
            ];
            return redirect('/ProductSavePage')->with($data);
        }
    }// end method
    public function ProductDelete(Request $request, $id){
        try {
            $user_id = $request->header('id');
            $product = Product::where('user_id', $user_id)->findOrFail($id);

            if($product->image && file_exists(public_path($product->image))){
                    unlink(public_path($product->image));
                }
            $product->delete();
            // return response()->json([
            //     'status' => 'Success',
            //     'message' => "Product deleted Successfully"
            // ], 200);
            $data = [
                'message' => "Product deleted Successfully",
                'status' => true,
                'error' => ''
            ];
            return redirect()->back()->with($data);
        } catch (Exception $e) {
            // return response()->json([
            //     'status' => "Failed",
            //     'message' => "Product dose not deleted, please try again later",
            //     // 'message' => $e->getMessage(),
            // ], 500);
            $data = [
                'message' => "Product dose not deleted, please try again later",
                'status' => false,
                'error' => ''
            ];
            return redirect()->back()->with($data);
        }
    }// end method

    public function ProductPage(){
        $user_id = request()->header('id');

        $products = Product::where('user_id', $user_id)->with('category')->latest()->get();
        return Inertia::render('ProductPage', ['products' => $products]);
    }

    public function ProductSavePage(Request $request){
        $user_id = request()->header('id');

        $categories = Category::where('user_id', $user_id)->get();

        $product = Product::where('id', $request->query('id'))->where('user_id', $user_id)->first();

        return Inertia::render('ProductSavePage', ['categories' => $categories, 'product' => $product]);
    }
}
