<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperatorController extends Controller
{
    public function index()
    {
        $operators = Operator::all();
        return Inertia::render('Operators/Index', ['operators' => $operators]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:operators,name',
            'business_model' => 'required|in:deposit_bonus,sale_commission',
            'default_percentage' => 'required|numeric|min:0|max:100',
            'percentages' => 'nullable|array',
            'percentages.*' => 'numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        // Auto-generate code from name (first 3 letters, uppercase)
        $validated['code'] = strtoupper(substr($validated['name'], 0, 3));
        
        $operator = Operator::create($validated);
        
        // Create operator rates if percentages provided
        if (!empty($validated['percentages'])) {
            foreach ($validated['percentages'] as $percentage) {
                \App\Models\OperatorRate::create([
                    'operator_id' => $operator->id,
                    'rate_type' => $validated['business_model'],
                    'percentage' => $percentage,
                    'is_active' => true,
                    'effective_from' => now(),
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'SIM Provider created successfully'
        ]);
    }

    public function update(Request $request, $id)
    {
        $operator = Operator::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:operators,name,' . $id,
            'business_model' => 'required|in:deposit_bonus,sale_commission',
            'default_percentage' => 'required|numeric|min:0|max:100',
            'percentages' => 'nullable|array',
            'percentages.*' => 'numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        // Auto-generate code from name (first 3 letters, uppercase)
        $validated['code'] = strtoupper(substr($validated['name'], 0, 3));
        
        $operator->update($validated);
        
        // Update operator rates
        if (!empty($validated['percentages'])) {
            // Delete existing rates
            $operator->rates()->delete();
            
            // Create new rates
            foreach ($validated['percentages'] as $percentage) {
                \App\Models\OperatorRate::create([
                    'operator_id' => $operator->id,
                    'rate_type' => $validated['business_model'],
                    'percentage' => $percentage,
                    'is_active' => true,
                    'effective_from' => now(),
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'SIM Provider updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $operator = Operator::findOrFail($id);
        
        // Check if operator has any wallet accounts
        if ($operator->walletAccounts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete SIM provider with existing wallet accounts'
            ], 422);
        }
        
        $operator->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'SIM Provider deleted successfully'
        ]);
    }

    public function toggleStatus(Operator $operator)
    {
        $operator->update(['is_active' => !$operator->is_active]);
        return redirect()->back()->with('success', 'Operator status updated');
    }
}
