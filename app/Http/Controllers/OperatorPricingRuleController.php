<?php

namespace App\Http\Controllers;

use App\Models\OperatorPricingRule;
use App\Models\Operator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperatorPricingRuleController extends Controller
{
    /**
     * Display pricing rules management page
     */
    public function index(Request $request)
    {
        $rules = OperatorPricingRule::orderBy('operator_name')
            ->orderBy('priority', 'desc')
            ->orderBy('face_value')
            ->get();

        // Get unique operator names
        $operatorNames = OperatorPricingRule::distinct()
            ->pluck('operator_name')
            ->sort()
            ->values();

        // If it's an API request, return JSON
        if ($request->is('api/*')) {
            return response()->json([
                'rules' => $rules,
                'operatorNames' => $operatorNames,
            ]);
        }

        return Inertia::render('SimActivation/ManagePricingRules', [
            'rules' => $rules,
            'operatorNames' => $operatorNames,
        ]);
    }

    /**
     * Store a new pricing rule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'operator_name' => 'required|string|max:255',
            'face_value' => 'nullable|numeric|min:0',
            'seller_discount_flat' => 'nullable|numeric|min:0',
            'seller_discount_percent' => 'nullable|numeric|min:0|max:100',
            'extra_benefit' => 'nullable|numeric|min:0',
            'package_cost_override' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'rule_description' => 'nullable|string|max:500',
        ]);

        // Auto-set rule_type based on face_value
        $validated['rule_type'] = $validated['face_value'] ? 'exact' : 'default';
        $validated['transaction_type'] = 'sim_activation'; // Always sim_activation

        // Set defaults
        $validated['seller_discount_flat'] = $validated['seller_discount_flat'] ?? 0;
        $validated['seller_discount_percent'] = $validated['seller_discount_percent'] ?? 0;
        $validated['extra_benefit'] = $validated['extra_benefit'] ?? 0;
        $validated['wallet_credit'] = 0; // No wallet credit
        $validated['priority'] = $validated['rule_type'] === 'exact' ? 100 : 0;

        $rule = OperatorPricingRule::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule created successfully',
            'rule' => $rule,
        ]);
    }

    /**
     * Update an existing pricing rule
     */
    public function update(Request $request, OperatorPricingRule $rule)
    {
        $validated = $request->validate([
            'operator_name' => 'required|string|max:255',
            'face_value' => 'nullable|numeric|min:0',
            'seller_discount_flat' => 'nullable|numeric|min:0',
            'seller_discount_percent' => 'nullable|numeric|min:0|max:100',
            'extra_benefit' => 'nullable|numeric|min:0',
            'package_cost_override' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'rule_description' => 'nullable|string|max:500',
        ]);

        // Auto-set rule_type based on face_value
        $validated['rule_type'] = $validated['face_value'] ? 'exact' : 'default';
        $validated['transaction_type'] = 'sim_activation'; // Always sim_activation

        $validated['seller_discount_flat'] = $validated['seller_discount_flat'] ?? 0;
        $validated['seller_discount_percent'] = $validated['seller_discount_percent'] ?? 0;
        $validated['extra_benefit'] = $validated['extra_benefit'] ?? 0;
        $validated['wallet_credit'] = 0; // No wallet credit
        $validated['priority'] = $validated['rule_type'] === 'exact' ? 100 : 0;

        $rule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule updated successfully',
            'rule' => $rule,
        ]);
    }

    /**
     * Delete a pricing rule
     */
    public function destroy(OperatorPricingRule $rule)
    {
        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule deleted successfully',
        ]);
    }

    /**
     * Toggle rule active status
     */
    public function toggleActive(OperatorPricingRule $rule)
    {
        $rule->is_active = !$rule->is_active;
        $rule->save();

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule status updated successfully',
            'rule' => $rule->load('operator'),
        ]);
    }
}
