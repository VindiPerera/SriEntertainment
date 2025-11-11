<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefillLaminating;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefillLaminatingController extends Controller
{
    public function index()
    {
        return view('refilllaminating.index', [
            'refills' => RefillLaminating::with('product')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock available. Available stock: ' . $product->stock_quantity
            ], 422);
        }

        $totalStock = RefillLaminating::where('product_code', $product->code)->sum('total_stock') + $validated['quantity'];

        $refill = RefillLaminating::create([
            'product_id' => $validated['product_id'],
            'product_code' => $product->code,
            'product_name' => $product->name,
            'quantity' => $validated['quantity'],
            'total_stock' => $totalStock,
        ]);

        $product->stock_quantity -= $validated['quantity'];
        $product->save();

        return response()->json([
            'message' => 'New refill added successfully',
            'refill' => $refill
        ], 201);
    }

    public function storeByCode(Request $request)
    {
        $validated = $request->validate([
            'product_code' => 'required|exists:products,code',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::where('code', $validated['product_code'])->firstOrFail();

        if ($product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock available. Available stock: ' . $product->stock_quantity
            ], 422);
        }

        $existingRefill = RefillLaminating::where('product_code', $validated['product_code'])->first();

        if ($existingRefill) {
            $existingRefill->update([
                'quantity' => $existingRefill->quantity + $validated['quantity'],
                'total_stock' => $existingRefill->total_stock + $validated['quantity'],
            ]);

            $refill = $existingRefill;
        } else {
            $refill = RefillLaminating::create([
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name,
                'quantity' => $validated['quantity'],
                'total_stock' => $validated['quantity'],
            ]);
        }

        $product->stock_quantity -= $validated['quantity'];
        $product->save();

        return response()->json([
            'message' => 'Refill added successfully',
            'refill' => $refill
        ], 201);
    }

    /**
     * API: Get all products with stock 0 (grouped by product code)
     */
    public function lowStockProducts()
    {
        // Get all unique product codes from refill_laminatings
        $productCodes = RefillLaminating::distinct('product_code')->pluck('product_code');
        
        $lowStockProducts = [];
        
        // For each product code, get the latest record and check if stock is 0
        foreach ($productCodes as $code) {
            $latestRefill = RefillLaminating::where('product_code', $code)
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
                
            // If the latest record has total_stock 0, add it to low stock list
            if ($latestRefill && $latestRefill->total_stock == 0) {
                $lowStockProducts[] = $latestRefill;
            }
        }

        return response()->json([
            'success' => true,
            'low_stock_products' => $lowStockProducts
        ]);
    }
}