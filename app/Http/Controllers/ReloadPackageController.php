<?php

namespace App\Http\Controllers;

use App\Models\ReloadPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReloadPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = ReloadPackage::with('operator')
            ->orderBy('operator_id')
            ->orderBy('sort_order')
            ->orderBy('face_value')
            ->get();

        return response()->json($packages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'operator_id' => 'required|exists:operators,id',
            'name' => 'required|string|max:255',
            'face_value' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        try {
            // Generate a unique code based on operator and face value
            $operator = \App\Models\Operator::find($validated['operator_id']);
            $code = strtoupper(substr($operator->name, 0, 3)) . '-' . number_format($validated['face_value'], 0);
            
            // Ensure unique code
            $baseCode = $code;
            $counter = 1;
            while (ReloadPackage::where('code', $code)->exists()) {
                $code = $baseCode . '-' . $counter;
                $counter++;
            }

            $package = ReloadPackage::create([
                'operator_id' => $validated['operator_id'],
                'name' => $validated['name'],
                'code' => $code,
                'face_value' => $validated['face_value'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'sort_order' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Package created successfully',
                'package' => $package->load('operator'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create package: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $package = ReloadPackage::with('operator')->findOrFail($id);
        return response()->json($package);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'operator_id' => 'required|exists:operators,id',
            'name' => 'required|string|max:255',
            'face_value' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        try {
            $package = ReloadPackage::findOrFail($id);
            
            // If operator or face value changed, regenerate code
            if ($package->operator_id != $validated['operator_id'] || $package->face_value != $validated['face_value']) {
                $operator = \App\Models\Operator::find($validated['operator_id']);
                $code = strtoupper(substr($operator->name, 0, 3)) . '-' . number_format($validated['face_value'], 0);
                
                // Ensure unique code
                $baseCode = $code;
                $counter = 1;
                while (ReloadPackage::where('code', $code)->where('id', '!=', $id)->exists()) {
                    $code = $baseCode . '-' . $counter;
                    $counter++;
                }
                
                $package->code = $code;
            }
            
            $package->update([
                'operator_id' => $validated['operator_id'],
                'name' => $validated['name'],
                'face_value' => $validated['face_value'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Package updated successfully',
                'package' => $package->fresh()->load('operator'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update package: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $package = ReloadPackage::findOrFail($id);
            $package->delete();

            return response()->json([
                'success' => true,
                'message' => 'Package deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete package: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get packages by operator
     */
    public function byOperator($operatorId)
    {
        $packages = ReloadPackage::where('operator_id', $operatorId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('face_value')
            ->get();

        return response()->json($packages);
    }
}
