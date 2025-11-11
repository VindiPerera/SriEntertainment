<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaminatingService;
use App\Models\LaminatingServiceRawMaterial;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;

class LaminatingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check for AJAX/JSON requests more reliably
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json(LaminatingService::all());
        }

        return Inertia::render('Laminating/Index', [
            'laminatingServices' => LaminatingService::paginate(10),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'pouch_size' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'service_amount' => 'required|numeric|min:0',
                'products' => 'required|array|min:1',
                'products.*' => 'required|exists:products,id',
            ]);

            // Use database transaction to ensure data integrity
            \DB::beginTransaction();
            
            try {
                // Create the laminating service
                $laminatingService = LaminatingService::create([
                    'name' => $validated['name'],
                    'pouch_size' => $validated['pouch_size'],
                    'price' => $validated['price'],
                    'service_amount' => $validated['service_amount'],
                    'service_id' => 3, // Laminating service ID
                ]);

                \Log::info('Laminating service created', ['service_id' => $laminatingService->id]);

                // Store selected products in raw materials table
                foreach ($validated['products'] as $productId) {
                    $rawMaterial = LaminatingServiceRawMaterial::create([
                        'laminating_service_id' => $laminatingService->id,
                        'product_id' => $productId,
                    ]);
                    
                    \Log::info('Raw material created', [
                        'raw_material_id' => $rawMaterial->id,
                        'service_id' => $laminatingService->id,
                        'product_id' => $productId
                    ]);
                }

                \DB::commit();

                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Laminating service created successfully',
                        'laminatingService' => $laminatingService->load('rawMaterials.product')
                    ], 201);
                }

                return redirect()->back()->with('success', 'Laminating service created successfully.');
                
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error', ['errors' => $e->errors()]);
            
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            \Log::error('Error creating laminating service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the laminating service',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, LaminatingService $laminatingService)
    {
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json($laminatingService);
        }

        return Inertia::render('Laminating/Show', [
            'laminatingService' => $laminatingService,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LaminatingService $laminatingService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pouch_size' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'service_amount' => 'required|numeric|min:0',
        ]);

        $laminatingService->update($validated);

        return redirect()->back()->with('success', 'Service updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LaminatingService $laminatingService)
    {
        $laminatingService->delete();

        return redirect()->back()->with('success', 'Service deleted successfully');
    }

    /**
     * Handle the refill stock functionality.
     */
    public function refillStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Check if product has enough stock
        if ($product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock available. Available stock: ' . $product->stock_quantity
            ], 422);
        }

        // Update stock
        $product->stock_quantity += $validated['quantity'];
        $product->save();

        return response()->json([
            'message' => 'Stock refilled successfully',
            'product' => $product
        ], 200);
    }

    /**
     * Fetch all categories.
     */
    public function fetchCategories()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    /**
     * Fetch products based on the selected category.
     */
    public function fetchProducts(Request $request)
    {
        $categoryId = $request->query('category_id');
        
        if (!$categoryId) {
            return response()->json([]);
        }
        
        $products = Product::where('category_id', $categoryId)
                          ->select('id', 'name', 'code', 'category_id')
                          ->get();
                          
        return response()->json($products);
    }
}