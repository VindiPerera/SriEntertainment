<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefillPrintout;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class RefillPrintoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $refills = RefillPrintout::with('product')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('refillprintout.index', compact('refills'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'product_name' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
            ]);

            // Use database transaction for data integrity
            DB::beginTransaction();

            // Find the product
            $product = Product::findOrFail($validated['product_id']);

            // Check the last total stock for the product
            $lastRefill = RefillPrintout::where('product_id', $validated['product_id'])
                ->orderBy('created_at', 'desc')
                ->first();

            $lastTotalStock = $lastRefill ? $lastRefill->total_stock : 0;

            // Calculate new total stock
            $newTotalStock = $lastTotalStock + $validated['quantity'];

            // Deduct the quantity from the product's stock
            $newStock = $product->stock_quantity - $validated['quantity'];

            // Create refill record with product_code
            $refill = RefillPrintout::create([
                'product_id' => $validated['product_id'],
                'product_code' => $product->code, // Add product code
                'product_name' => $validated['product_name'],
                'quantity' => $validated['quantity'],
                'total_stock' => $newTotalStock,
            ]);

            // Update the product's stock quantity (DEDUCT from stock)
            $product->stock_quantity = $newStock;
            $product->save();

            DB::commit();

            return response()->json([
                'message' => 'Stock refilled successfully',
                'refill' => $refill,
                'new_stock' => $product->stock_quantity,
                'previous_stock' => $newStock + $validated['quantity'],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to refill stock',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternative method to refill by product code
     */
    public function storeByCode(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_code' => 'required|string',
                'quantity' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            // Check if the product code exists
            $product = Product::where('code', $validated['product_code'])->first();

            if (!$product) {
                return response()->json([
                    'error' => 'Product not found',
                    'message' => 'The provided product code does not exist.'
                ], 404);
            }

            // Check if there is enough stock to deduct
            if ($product->stock_quantity < $validated['quantity']) {
                return response()->json([
                    'error' => 'Insufficient stock',
                    'message' => 'Available stock: ' . $product->stock_quantity
                ], 422);
            }

            // Deduct the quantity from the product's stock
            $newStock = $product->stock_quantity - $validated['quantity'];

            // Create refill record
            $refill = RefillPrintout::create([
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name,
                'quantity' => $validated['quantity'],
                'total_stock' => $newStock,
            ]);

            // Update the product's stock quantity
            $product->stock_quantity = $newStock;
            $product->save();

            DB::commit();

            return response()->json([
                'message' => 'Stock refilled successfully and deducted from product stock',
                'refill' => $refill,
                'new_stock' => $product->stock_quantity,
                'previous_stock' => $newStock + $validated['quantity'],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to refill stock',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get all products with stock 0 (grouped by product code)
     */
    public function lowStockProducts()
    {
        // Get all unique product codes from refill_printouts
        $productCodes = RefillPrintout::distinct('product_code')->pluck('product_code');
        
        $lowStockProducts = [];
        
        // For each product code, get the latest record and check if stock is 0
        foreach ($productCodes as $code) {
            $latestRefill = RefillPrintout::where('product_code', $code)
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