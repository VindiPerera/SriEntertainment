<?php

namespace App\Http\Controllers;

use App\Models\SimStock;
use App\Models\SimStockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SimStockController extends Controller
{
    /**
     * Display a listing of the SIM stocks
     */
    public function index()
    {
        $simStocks = SimStock::orderBy('created_at', 'desc')->get();
        
        // Get all stock movements with SIM stock details
        $stockMovements = SimStockMovement::with('simStock')
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('SimStock/ManageSim', [
            'simStocks' => $simStocks,
            'stockMovements' => $stockMovements,
            'pageTitle' => 'Manage SIM Cards'
        ]);
    }

    /**
     * Store a newly created SIM stock
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sim_name' => 'required|in:Mobitel,Dialog,Airtel,Hutch',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'purchase_date' => 'required|date'
        ]);

        // Generate automatic batch number
        $validated['batch_number'] = $this->generateBatchNumber($validated['sim_name']);

        $simStock = SimStock::create($validated);

        // Record stock movement for initial stock
        SimStockMovement::create([
            'sim_stock_id' => $simStock->id,
            'movement_type' => 'in',
            'quantity' => $validated['stock'],
            'previous_stock' => 0,
            'current_stock' => $validated['stock'],
            'reference_type' => 'initial_stock',
            'notes' => 'Initial stock entry',
            'movement_date' => now()
        ]);

        return redirect()->back()->with('success', 'SIM stock added successfully');
    }

    /**
     * Update the specified SIM stock
     */
    public function update(Request $request, SimStock $simStock)
    {
        $validated = $request->validate([
            'sim_name' => 'required|in:Mobitel,Dialog,Airtel,Hutch',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'purchase_date' => 'required|date'
        ]);

        // Track stock changes
        $previousStock = $simStock->stock;
        $newStock = $validated['stock'];

        // If carrier changed, regenerate batch number
        if ($validated['sim_name'] !== $simStock->sim_name) {
            $validated['batch_number'] = $this->generateBatchNumber($validated['sim_name']);
        }

        $simStock->update($validated);

        // Record stock movement if quantity changed
        if ($previousStock != $newStock) {
            $difference = $newStock - $previousStock;
            
            SimStockMovement::create([
                'sim_stock_id' => $simStock->id,
                'movement_type' => $difference > 0 ? 'in' : 'out',
                'quantity' => abs($difference),
                'previous_stock' => $previousStock,
                'current_stock' => $newStock,
                'reference_type' => 'adjustment',
                'notes' => $difference > 0 ? 'Stock increased by manual adjustment' : 'Stock decreased by manual adjustment',
                'movement_date' => now()
            ]);
        }

        return redirect()->back()->with('success', 'SIM stock updated successfully');
    }

    /**
     * Remove the specified SIM stock
     */
    public function destroy(SimStock $simStock)
    {
        $simStock->delete();

        return redirect()->back()->with('success', 'SIM stock deleted successfully');
    }

    /**
     * Generate automatic batch number
     */
    private function generateBatchNumber($simName)
    {
        // Get carrier prefix
        $prefixes = [
            'Mobitel' => 'MB',
            'Dialog' => 'DG',
            'Airtel' => 'AT',
            'Hutch' => 'HT'
        ];
        
        $prefix = $prefixes[$simName];
        $year = date('Y');
        $month = date('m');
        
        // Get the last batch number for this carrier and year-month
        $lastBatch = SimStock::where('sim_name', $simName)
            ->where('batch_number', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('batch_number', 'desc')
            ->first();
        
        if ($lastBatch) {
            // Extract the sequence number and increment
            $lastNumber = intval(substr($lastBatch->batch_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        // Format: PREFIX-YYYYMM-0001
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $newNumber);
    }
}
