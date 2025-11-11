<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefillPhotocopy;
use App\Models\Product;

class RefillPhotocopyController extends Controller
{
    /**
     * API: Get all products with stock 0 (grouped by product code)
     */
    public function lowStockProducts()
    {
        // Get all unique product codes from refill_photocopies
        $productCodes = RefillPhotocopy::distinct('product_code')->pluck('product_code');
        
        $lowStockProducts = [];
        
        // For each product code, get the latest record and check if stock is 0
        foreach ($productCodes as $code) {
            $latestRefill = RefillPhotocopy::where('product_code', $code)
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
                
            // If the latest record has stock 0, add it to low stock list
            if ($latestRefill && $latestRefill->stock == 0) {
                $lowStockProducts[] = $latestRefill;
            }
        }

        return response()->json([
            'success' => true,
            'low_stock_products' => $lowStockProducts
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('refillphotocopy.index', [
            'refills' => RefillPhotocopy::with('product')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
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
                'message' => 'Insufficient stock available. Available stock: ' . $product->stock
            ], 422);
        }

        // Calculate total stock for the product
        $totalStock = RefillPhotocopy::where('product_code', $product->code)->sum('stock') + $validated['quantity'];

        // Always create a new refill record
        $refill = RefillPhotocopy::create([
            'product_id' => $validated['product_id'],
            'product_code' => $product->code,
            'product_name' => $product->name,
            'quantity' => $validated['quantity'],
            'stock' => $totalStock,
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
     * Alternative method using product code instead of product_id
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
        $existingRefill = RefillPhotocopy::where('product_code', $validated['product_code'])->first();

        if ($existingRefill) {
            // Update existing refill record
            $existingRefill->update([
                'quantity' => $existingRefill->quantity + $validated['quantity'],
                'stock' => $existingRefill->stock + $validated['quantity'],
            ]);

            $refill = $existingRefill;
        } else {
            // Create new refill record
            $refill = RefillPhotocopy::create([
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name,
                'quantity' => $validated['quantity'],
                'stock' => $validated['quantity'],
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