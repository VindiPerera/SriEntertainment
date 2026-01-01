<?php

namespace App\Http\Controllers;

use App\Models\Newspaper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\ReturnItem;

class NewspaperController extends Controller
{
    /**
     * Get the next batch number for a product code
     */
    public function getNextBatchNumber(Request $request)
    {
        $productcode = $request->query('productcode');
        
        $latestBatch = Newspaper::where('productcode', $productcode)
            ->orderBy('created_at', 'desc')
            ->value('batch_no');
            
        $nextBatch = $latestBatch ? ((int)$latestBatch + 1) : 1;
        
        return response()->json(['batch_no' => $nextBatch]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 12); // Default to 12 items per page
        $query = Newspaper::query();

        // Apply search filter if search term is provided
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('productcode', 'like', '%' . $searchTerm . '%')
                  ->orWhere('language', 'like', '%' . $searchTerm . '%');
            });
        }

        $newspapers = $query->paginate($perPage);
        
        return Inertia::render('Newspaper/Index', [
            'newspapers' => $newspapers,
            'totalNewspapers' => Newspaper::count(),
            'search' => $request->input('search'),
            'perPage' => $perPage,
        ]);
    }

    /**
     * Get newspapers available for return (stock > 0)
     */
    public function getAvailableForReturn()
    {
        $newspapers = Newspaper::where('stock_quantity', '>', 0)
            ->select('id', 'name', 'stock_quantity', 'day_of_week')
            ->orderBy('name')
            ->get();
            
        return response()->json($newspapers);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = \App\Models\Supplier::orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Newspaper/Create', [
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Get suppliers for dropdown
     */
    public function getSuppliers()
    {
        $suppliers = \App\Models\Supplier::orderBy('name')->get(['id', 'name']);
        return response()->json($suppliers);
    }

    /**
     * Get newspaper names for dropdown
     */
    public function getNewspaperNames()
    {
        $newspapers = Newspaper::orderBy('name')->get(['id', 'name']);
        return response()->json($newspapers);
    }

    /**
     * Get store newspapers for the store modal
     */
    public function getStoreNewspapers()
    {
        $storeNewspapers = \App\Models\StoreNewspaper::orderBy('name')
            ->get(['id', 'name']);
            
        return response()->json($storeNewspapers);
    }

    /**
     * Store a newspaper in the store newspapers table
     */
    public function storeNewspaper(Request $request)
    {
        \Log::info('Store newspaper request received:', $request->all());
        
        // Validate first - let Laravel handle validation errors with 422 response
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:store_newspapers,name',
        ]);

        \Log::info('Validation passed:', $validated);

        try {
            $storeNewspaper = \App\Models\StoreNewspaper::create([
                'name' => $validated['name'],
            ]);

            \Log::info('Store newspaper created:', $storeNewspaper->toArray());

            return response()->json($storeNewspaper, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating store newspaper:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:newspapers,name',
            'productcode' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'batch_no' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'duration' => 'required|in:monthly,weekly',
            'day_of_week' => 'required_if:duration,weekly|nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'publication_date' => 'required|date',
            'expire_date' => 'required|date',
            'language' => 'nullable|in:tamil,sinhala,english',
            'stock_quantity' => 'required|integer',
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'discount_price' => 'nullable|numeric',
            'return' => 'required|integer|min:0'
        ]);

        Newspaper::create([
            'name' => $validated['name'],
            'productcode' => $validated['productcode'],
            'barcode' => $validated['barcode'],
            'batch_no' => $validated['batch_no'],
            'supplier' => $validated['supplier'],
            'duration' => $validated['duration'],
            'day_of_week' => $validated['day_of_week'],
            'publication_date' => $validated['publication_date'],
            'expire_date' => $validated['expire_date'],
            'language' => $validated['language'],
            'stock_quantity' => $validated['stock_quantity'],
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'discount' => $validated['discount'],
            'discount_price' => $validated['discount_price'],
            'return' => $validated['return'],
        ]);

        return redirect()->route('newspapers.index')->with('success', 'Newspaper created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Newspaper $newspaper)
    {
        return Inertia::render('Newspaper/Show', [
            'newspaper' => $newspaper,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Newspaper $newspaper)
    {
        $suppliers = \App\Models\Supplier::orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Newspaper/Edit', [
            'newspaper' => $newspaper->only([
                'id', 'name', 'productcode', 'barcode', 'batch_no', 'supplier', 'duration', 'day_of_week',
                'publication_date', 'expire_date', 'language', 'stock_quantity', 'cost_price', 'selling_price',
                'discount', 'discount_price', 'return'
            ]),
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Newspaper $newspaper)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'productcode' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'batch_no' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'duration' => 'required|in:monthly,weekly',
            'day_of_week' => 'required_if:duration,weekly|nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'publication_date' => 'required|date',
            'expire_date' => 'required|date',
            'language' => 'nullable|in:tamil,sinhala,english',
            'stock_quantity' => 'required|integer',
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'discount_price' => 'nullable|numeric',
        ]);

        $newspaper->update($validated);

        return redirect()->route('newspapers.index')->with('success', 'Newspaper updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Newspaper $newspaper)
    {
        $newspaper->delete();

        return redirect()->route('newspapers.index')->with('success', 'Newspaper deleted successfully.');
    }

    /**
     * Process the return of a newspaper
     */
    public function return(Request $request)
    {
         $validated = $request->validate([
        'newspaper_ids' => 'required|array|min:1',
        'newspaper_ids.*' => 'required|exists:newspapers,id',
        'reason' => 'required|string',
    ]);

    DB::transaction(function () use ($validated) {
        foreach ($validated['newspaper_ids'] as $newspaperId) {
            $newspaper = Newspaper::find($newspaperId);
            
            // Get the current stock quantity (this is what we're returning)
            $returnQuantity = $newspaper->stock_quantity;
            
            // Only process if there's stock to return
            if ($returnQuantity > 0) {
                // Create return record for each newspaper
                ReturnItem::create([
                    'newspaper_id' => $newspaperId,
                    'quantity' => $returnQuantity,
                    'reason' => $validated['reason'],
                    'return_date' => now(),
                ]);

                // Increment the return field by the stock quantity
                $newspaper->increment('return', $returnQuantity);

                // Set stock quantity to 0 (all stock returned)
                $newspaper->update(['stock_quantity' => 0]);
            }
        }
    });

    return back()->with('success', 'Newspapers returned successfully');

         }
}