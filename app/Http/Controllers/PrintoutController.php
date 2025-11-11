<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PrintoutService;
use App\Models\PrintoutServiceRawMaterial;
use App\Models\Category;
use App\Models\Product;

class PrintoutController extends Controller
{
    /**
     * Display a listing of the printout services.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $printoutServices = PrintoutService::all();
            return response()->json($printoutServices);
        }

        $printoutServices = PrintoutService::paginate(10);
        return Inertia::render('Services/Printout', [
            'printoutServices' => $printoutServices,
        ]);
    }

    /**
     * Store a newly created printout service in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'size' => 'required|string|max:255',
                'side' => 'required|string|max:255',
                'pages' => 'required|string|max:255',
                'color' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'service_charge' => 'required|numeric|min:0',
                'products' => 'required|array|min:1',
                'products.*' => 'required|exists:products,id',
            ]);

            // Use database transaction to ensure data integrity
            \DB::beginTransaction();
            
            try {
                // Create the printout service
                $printoutService = PrintoutService::create([
                    'name' => $validated['name'],
                    'size' => $validated['size'],
                    'side' => $validated['side'],
                    'pages' => $validated['pages'],
                    'color' => $validated['color'],
                    'price' => $validated['price'],
                    'service_charge' => $validated['service_charge'],
                    'service_id' => 2, // Printout service ID
                ]);

                \Log::info('Printout service created', ['service_id' => $printoutService->id]);

                // Store selected products in raw materials table
                foreach ($validated['products'] as $productId) {
                    $rawMaterial = PrintoutServiceRawMaterial::create([
                        'printout_service_id' => $printoutService->id,
                        'product_id' => $productId,
                    ]);
                    
                    \Log::info('Raw material created', [
                        'raw_material_id' => $rawMaterial->id,
                        'service_id' => $printoutService->id,
                        'product_id' => $productId
                    ]);
                }

                \DB::commit();

                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Printout service created successfully',
                        'printoutService' => $printoutService->load('rawMaterials.product')
                    ], 201);
                }

                return redirect('/services/printout')->with('success', 'Printout service created successfully.');
                
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
            \Log::error('Error creating printout service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the printout service',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified printout service in storage.
     */
    public function update(Request $request, PrintoutService $printoutService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'size' => 'required|string',
            'side' => 'required|string',
            'pages' => 'required|string',
            'color' => 'required|string',
            'price' => 'required|numeric|min:0',
            'service_charge' => 'required|numeric|min:0',
        ]);

        $printoutService->update($request->all());

        if ($request->expectsJson()) {
            return response()->json($printoutService);
        }

        return redirect()->back()->with('success', 'Printout service updated successfully.');
    }

    /**
     * Remove the specified printout service from storage.
     */
    public function destroy(PrintoutService $printoutService)
    {
        $printoutService->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back()->with('success', 'Printout service deleted successfully.');
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