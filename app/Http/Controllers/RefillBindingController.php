<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefillBinding;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class RefillBindingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $refills = RefillBinding::with('product')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('refillbinding.index', compact('refills'));
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

            // Check if refill record already exists for this product
            $existingRefill = RefillBinding::where('product_id', $validated['product_id'])->first();

            if ($existingRefill) {
                // Update existing refill record
                $existingRefill->quantity += $validated['quantity'];
                $existingRefill->total_stock = $product->quantity + $validated['quantity'];
                $existingRefill->save();
            } else {
                // Create new refill record
                RefillBinding::create([
                    'product_id' => $validated['product_id'],
                    'product_code' => $product->code,
                    'product_name' => $validated['product_name'],
                    'quantity' => $validated['quantity'],
                    'total_stock' => $product->quantity + $validated['quantity'],
                ]);
            }

            // Update product stock
            $product->quantity += $validated['quantity'];
            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock refilled successfully!',
                'data' => [
                    'product_name' => $validated['product_name'],
                    'quantity_added' => $validated['quantity'],
                    'new_total_stock' => $product->quantity,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error refilling binding stock: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while refilling stock. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get all products with stock 0 (grouped by product code)
     */
    public function lowStockProducts()
    {
        // Get all unique product codes from refill_bindings
        $productCodes = RefillBinding::distinct('product_code')->pluck('product_code');
        
        $lowStockProducts = [];
        
        // For each product code, get the latest record and check if stock is 0
        foreach ($productCodes as $code) {
            $latestRefill = RefillBinding::where('product_code', $code)
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
