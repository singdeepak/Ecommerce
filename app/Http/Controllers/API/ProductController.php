<?php

namespace App\Http\Controllers\API;

use Throwable;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::where('is_active', true)
                ->with(['category', 'images'])
                ->get();

            return response()->json([
                'status' => 'success',
                'status_code' => 200,
                'message' => 'Product retrived successfully.',
                'products' => $products
            ], 200);
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Something went wrong.',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function show($slug)
    {
        try {
            $product = Product::where('slug', $slug)
                ->where('is_active', true)
                ->with(['category', 'images'])
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or inactive.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'status_code' => 200,
                'message' => 'Product retrived successfully.',
                'product' => $product
            ], 200);
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Something went wrong.',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function productsByCategory($slug)
    {
        try {
            $category = Category::where('slug', $slug)->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.'
                ], 404);
            }

            $products = $category->products()
                ->where('is_active', true)
                ->with(['images'])
                ->paginate(15);

            return response()->json([
                'status' => 'success',
                'status_code' => 200,
                'message' => 'Category product retrived successfully.',
                'category' => $category->name,
                'products' => $products
            ], 200);
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Something went wrong.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
