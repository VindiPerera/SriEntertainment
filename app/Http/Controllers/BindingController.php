<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BindingService;
use App\Models\BindingServiceRawMaterial;
use App\Models\Category;
use App\Models\Product;

class BindingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $bindingServices = BindingService::all();
            return response()->json($bindingServices);
        }

        $bindingServices = BindingService::paginate(10);
        return Inertia::render('Services/Binding', [
            'bindingServices' => $bindingServices,
        ]);
    }

    /**
     * Store a newly created binding service in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'binding_type' => 'required|in:spiral,tape',
        'pages' => 'required|string',
        'price' => 'required|numeric|min:0',
        'service_charge' => 'required|numeric|min:0',
        'products' => 'required|array|min:1',  // Changed from 'selected_products' to 'products'
        'products.*' => 'exists:products,id',   // Changed from 'selected_products.*' to 'products.*'
    ]);

    try {
        // Use database transaction to ensure data integrity
        \DB::beginTransaction();
        
        try {
            // Create the binding service
            $service = BindingService::create($request->only([
                'name', 'binding_type', 'pages', 'price', 'service_charge'
            ]));

            \Log::info('Binding service created', ['service_id' => $service->id]);

            // Create raw material relationships for selected products
            if ($request->has('products') && !empty($request->products)) {
                foreach ($request->products as $productId) {
                    $rawMaterial = BindingServiceRawMaterial::create([
                        'binding_service_id' => $service->id,
                        'product_id' => $productId,
                    ]);
                    
                    \Log::info('Raw material created', [
                        'raw_material_id' => $rawMaterial->id,
                        'service_id' => $service->id,
                        'product_id' => $productId
                    ]);
                }
            }

            \DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Binding service created successfully!',
                    'data' => [
                        'bindingService' => $service->load('rawMaterials.product')
                    ]
                ], 201);
            }

            return redirect()->back()->with('success', 'Binding service created successfully.');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error', ['errors' => $e->errors()]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        return redirect()->back()->withErrors($e->errors())->withInput();
        
    } catch (\Exception $e) {
        \Log::error('Error creating binding service', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create binding service',
                'error' => $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Failed to create binding service: ' . $e->getMessage());
    }
}

    /**
     * Display the specified binding service.
     */
    public function show(BindingService $bindingService)
    {
        return response()->json($bindingService);
    }

    /**
     * Update the specified binding service in storage.
     */
    public function update(Request $request, BindingService $bindingService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'binding_type' => 'required|in:spiral,tape',
            'pages' => 'required|string',
            'price' => 'required|numeric|min:0',
            'service_charge' => 'required|numeric|min:0',
        ]);

        $bindingService->update($request->only([
            'name', 'binding_type', 'pages', 'price', 'service_charge'
        ]));

        if ($request->expectsJson()) {
            return response()->json($bindingService);
        }

        return redirect()->back()->with('success', 'Binding service updated successfully.');
    }

    /**
     * Refill stock for binding service raw materials.
     */
    public function refillStock(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'product_name' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
            ]);

            // Find the product
            $product = Product::findOrFail($validated['product_id']);
            
            // Update product stock
            $product->quantity += $validated['quantity'];
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Stock refilled successfully!',
                'data' => [
                    'product_name' => $validated['product_name'],
                    'quantity_added' => $validated['quantity'],
                    'new_total_stock' => $product->quantity,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error refilling binding stock: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while refilling stock. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified binding service from storage.
     */
    public function destroy(BindingService $bindingService)
    {
        $bindingService->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back()->with('success', 'Binding service deleted successfully.');
    }

    public function fetchProducts(Request $request)
    {
        $categoryId = $request->input('category_id');

        if (!$categoryId) {
            return response()->json([]);
        }

        $products = Product::select('id', 'name', 'code', 'category_id', 'selling_price', 'barcode')
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }
}