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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Newspaper/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'productcode' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'batch_no' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'duration' => 'required|in:monthly,weekly',
            'publication_date' => 'required|date',
            'expire_date' => 'required|date',
            'language' => 'nullable|string|max:255',
            'stock_quantity' => 'required|integer',
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'discount_price' => 'nullable|numeric',
            'return' => 'required|integer|min:0'
        ]);

        Newspaper::create($validated);

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
        return Inertia::render('Newspaper/Edit', [
            'newspaper' => $newspaper,
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
            'publication_date' => 'required|date',
            'expire_date' => 'required|date',
            'language' => 'nullable|string|max:255',
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
            'newspaper_id' => 'required|exists:newspapers,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        DB::transaction(function () use ($validated) {
            // Create return record
            ReturnItem::create([
                'newspaper_id' => $validated['newspaper_id'],
                'quantity' => $validated['quantity'],
                'reason' => $validated['reason'],
                'return_date' => now(),
            ]);

            // Update newspaper stock
            $newspaper = Newspaper::find($validated['newspaper_id']);
            $newspaper->increment('return', $validated['quantity']);
        });

        return back()->with('success', 'Newspaper return processed successfully');
    }
}