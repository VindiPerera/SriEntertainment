<?php

namespace App\Http\Controllers;

use App\Models\BindingRefill;
use App\Models\Product;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;

class BindingRefillController extends Controller
{
    /**
     * Fetch products with filters for binding refill.
     */
    public function fetchProducts(Request $request)
    {
        $perPage = 10;
        
        $query = Product::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('barcode', 'like', "%$search%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Stock status filter
        if ($request->filled('stock')) {
            if ($request->stock === 'in') {
                $query->where('stock_quantity', '>', 0);
            } elseif ($request->stock === 'out') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        // Color filter
        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }

        // Size filter
        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        // Sort by price
        if ($request->filled('sort')) {
            if ($request->sort === 'asc') {
                $query->orderBy('selling_price', 'asc');
            } elseif ($request->sort === 'desc') {
                $query->orderBy('selling_price', 'desc');
            }
        }

        $products = $query->paginate($perPage);

        // Get filter options
        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    /**
     * Store a new binding refill record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Get the product details
        $product = Product::findOrFail($validated['product_id']);

        // Check if product has enough stock
        if ($product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock available. Available stock: ' . $product->stock_quantity
            ], 422);
        }

        // Calculate total stock for the product
        $totalStock = BindingRefill::where('product_code', $product->code)->sum('total_stock') + $validated['quantity'];

        // Always create a new refill record
        $refill = BindingRefill::create([
            'product_id' => $validated['product_id'],
            'product_code' => $product->code,
            'product_name' => $product->name,
            'quantity' => $validated['quantity'],
            'total_stock' => $totalStock,
        ]);

        // Deduct the refill quantity from the product's available stock
        $product->stock_quantity -= $validated['quantity'];
        $product->save();

        return response()->json([
            'message' => 'New refill added successfully',
            'refill' => $refill
        ], 201);
    }

    /**
     * Store a new binding refill record using product code.
     */
    public function storeByCode(Request $request)
    {
        $validated = $request->validate([
            'product_code' => 'required|exists:products,code',
            'quantity' => 'required|integer|min:1',
        ]);

        // Get the product by code
        $product = Product::where('code', $validated['product_code'])->firstOrFail();

        // Check if product has enough stock
        if ($product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock available. Available stock: ' . $product->stock_quantity
            ], 422);
        }

        // Check if refill record already exists for this product
        $existingRefill = BindingRefill::where('product_code', $validated['product_code'])->first();

        if ($existingRefill) {
            // Update existing refill record
            $existingRefill->update([
                'quantity' => $existingRefill->quantity + $validated['quantity'],
                'total_stock' => $existingRefill->total_stock + $validated['quantity'],
            ]);

            $refill = $existingRefill;
        } else {
            // Create new refill record
            $refill = BindingRefill::create([
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name,
                'quantity' => $validated['quantity'],
                'total_stock' => $validated['quantity'],
            ]);
        }

        // Deduct the refill quantity from the product's available stock
        $product->stock_quantity -= $validated['quantity'];
        $product->save();

        return response()->json([
            'message' => 'Refill added successfully',
            'refill' => $refill
        ], 201);
    }
}