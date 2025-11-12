<?php

namespace App\Http\Controllers;

use App\Models\SimActivationPackage;
use App\Models\Operator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SimActivationPackageController extends Controller
{
    /**
     * Display package management page
     */
    public function index(Request $request)
    {
        $packages = SimActivationPackage::orderBy('operator_name')
            ->orderBy('sort_order')
            ->orderBy('face_value')
            ->get();

        // Get unique operator names from pricing rules
        $operatorNames = \App\Models\OperatorPricingRule::where('is_active', true)
            ->distinct()
            ->pluck('operator_name')
            ->sort()
            ->values();

        // If it's an API request, return JSON
        if ($request->is('api/*')) {
            return response()->json([
                'packages' => $packages,
                'operatorNames' => $operatorNames,
            ]);
        }

        return Inertia::render('SimActivation/ManagePackages', [
            'packages' => $packages,
            'operatorNames' => $operatorNames,
        ]);
    }

    /**
     * Store a new package
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'operator_name' => 'required|string|max:255',
            'package_name' => 'required|string|max:255',
            'face_value' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $package = SimActivationPackage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Package created successfully',
            'package' => $package,
        ]);
    }

    /**
     * Update an existing package
     */
    public function update(Request $request, SimActivationPackage $package)
    {
        $validated = $request->validate([
            'operator_name' => 'required|string|max:255',
            'package_name' => 'required|string|max:255',
            'face_value' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $package->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Package updated successfully',
            'package' => $package,
        ]);
    }

    /**
     * Delete a package
     */
    public function destroy(SimActivationPackage $package)
    {
        // Check if package has transactions
        if ($package->transactions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete package with existing transactions. Deactivate it instead.',
            ], 422);
        }

        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Package deleted successfully',
        ]);
    }

    /**
     * Toggle package active status
     */
    public function toggleActive(SimActivationPackage $package)
    {
        $package->is_active = !$package->is_active;
        $package->save();

        return response()->json([
            'success' => true,
            'message' => 'Package status updated successfully',
            'package' => $package->load('operator'),
        ]);
    }
}
