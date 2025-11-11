<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PhotocopyService;
use App\Models\Category;
use App\Models\Product;

class PhotocopyServiceController extends Controller
{
    /**
     * Display a listing of the photocopy services.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            $photocopyServices = PhotocopyService::all();
            return response()->json([
                'success' => true,
                'photocopyServices' => $photocopyServices
            ]);
        }

        $photocopyServices = PhotocopyService::paginate(10);
        return Inertia::render('Services/Photocopy', [
            'photocopyServices' => $photocopyServices,
        ]);
    }

    /**
     * Store a newly created photocopy service in storage.
     */
    /**
 * Store a newly created photocopy service in storage.
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
            // Create the photocopy service
            $photocopyService = PhotocopyService::create([
                'name' => $validated['name'],
                'size' => $validated['size'],
                'side' => $validated['side'],
                'pages' => $validated['pages'],
                'color' => $validated['color'],
                'price' => $validated['price'],
                'service_charge' => $validated['service_charge'],
                'service_id' => 1,
            ]);

            \Log::info('Photocopy service created', ['service_id' => $photocopyService->id]);

            // Store selected products in raw materials table
            foreach ($validated['products'] as $productId) {
                $rawMaterial = \App\Models\PhotocopyServiceRawMaterial::create([
                    'photocopy_service_id' => $photocopyService->id,
                    'product_id' => $productId,
                ]);
                
                \Log::info('Raw material created', [
                    'raw_material_id' => $rawMaterial->id,
                    'service_id' => $photocopyService->id,
                    'product_id' => $productId
                ]);
            }

            \DB::commit();

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Photocopy service created successfully',
                    'photocopyService' => $photocopyService->load('rawMaterials.product')
                ], 201);
            }

            return redirect('/services/photocopy')->with('success', 'Photocopy service created successfully.');
            
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
        \Log::error('Error creating photocopy service', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the photocopy service',
                'error' => $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
    }
}

    /**
     * Display the specified photocopy service.
     */
    public function show(PhotocopyService $photocopyService)
    {
        return response()->json($photocopyService);
    }

    /**
     * Update the specified photocopy service in storage.
     */
    public function update(Request $request, PhotocopyService $photocopyService)
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

        $photocopyService->update($request->all());

        if ($request->expectsJson()) {
            return response()->json($photocopyService);
        }

        return redirect()->back()->with('success', 'Photocopy service updated successfully.');
    }

    /**
     * Remove the specified photocopy service from storage.
     */
    public function destroy(PhotocopyService $photocopyService)
    {
        $photocopyService->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back()->with('success', 'Photocopy service deleted successfully.');
    }

    /**
     * Fetch all categories.
     */
    public function fetchCategories()
    {
        $categories = Category::with('parent')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent' => $category->parent ? [
                        'id' => $category->parent->id,
                        'name' => $category->parent->name,
                    ] : null,
                    'hierarchy_string' => $category->hierarchy_string,
                ];
            });

        return response()->json([
            'categories' => $categories
        ]);
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
                          ->select('id', 'name', 'code', 'category_id', 'stock_quantity', 'selling_price')
                          ->get();
                          
        return response()->json($products);
    }
}