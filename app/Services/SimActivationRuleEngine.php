<?php

namespace App\Services;

use App\Models\OperatorPricingRule;
use App\Models\SimStock;

class SimActivationRuleEngine
{
    /**
     * Match the best pricing rule for a transaction
     * Priority: exact value > percentage > default
     */
    public function matchRule($operatorName, $faceValue, $transactionType = 'sim_activation')
    {
        // Get all active rules for this operator and transaction type
        $rules = OperatorPricingRule::where('operator_name', $operatorName)
            ->where('transaction_type', $transactionType)
            ->where('is_active', true)
            ->byPriority()
            ->get();

        // First, try exact match
        $exactMatch = $rules->where('rule_type', 'exact')
            ->where('face_value', $faceValue)
            ->first();
        
        if ($exactMatch) {
            return $exactMatch;
        }

        // Second, try percentage match
        $percentMatch = $rules->where('rule_type', 'percentage')
            ->where('face_value', $faceValue)
            ->first();
        
        if ($percentMatch) {
            return $percentMatch;
        }

        // Finally, get default rule
        $defaultMatch = $rules->where('rule_type', 'default')
            ->whereNull('face_value')
            ->first();
        
        return $defaultMatch;
    }

    /**
     * Calculate transaction details based on matched rule
     */
    public function calculateTransaction($data)
    {
        $operatorName = $data['operator_name'];
        $pricingRuleId = $data['pricing_rule_id'] ?? null;
        $sim = isset($data['sim_stock_id']) ? SimStock::find($data['sim_stock_id']) : null;
        
        // Get wallet by operator name - need to find operator first
        $wallet = null;
        if (isset($data['user_id'])) {
            // Find operator by name to get wallet
            $operator = \App\Models\Operator::where('name', $operatorName)->first();
            if ($operator) {
                $wallet = \App\Models\WalletAccount::where('user_id', $data['user_id'])
                    ->where('operator_id', $operator->id)
                    ->first();
            }
        }

        $faceValue = $data['face_value'] ?? 0;
        $transactionType = 'sim_activation';

        // Get pricing rule directly if ID provided, otherwise match
        if ($pricingRuleId) {
            $rule = OperatorPricingRule::find($pricingRuleId);
        } else {
            $rule = $this->matchRule($operatorName, $faceValue, $transactionType);
        }

        // Calculate seller discount
        $sellerDiscountFlat = $rule ? $rule->seller_discount_flat : 0;
        $sellerDiscountPercent = $rule ? $rule->seller_discount_percent : 0;
        $sellerDiscountFromPercent = ($faceValue * $sellerDiscountPercent) / 100;
        $sellerDiscountTotal = $sellerDiscountFlat + $sellerDiscountFromPercent;
        
        // Extra benefit
        $extraBenefit = $rule ? $rule->extra_benefit : 0;
        
        // Wallet credit (can be positive for credits)
        $walletCredit = $rule ? $rule->wallet_credit : 0;

        // Calculate wallet deduction/credit
        // Amount to deduct = face_value - seller_discount - extra_benefit
        $netDeduction = $faceValue - $sellerDiscountTotal - $extraBenefit;
        
        // wallet_change = if wallet_credit > 0, it's a credit, otherwise it's negative deduction
        if ($walletCredit > 0) {
            $walletChange = $walletCredit;
        } else {
            $walletChange = -$netDeduction; // Negative because it's a deduction
        }
        
        $walletChange = round($walletChange, 2);
        $sellerDiscountTotal = round($sellerDiscountTotal, 2); // Round for display only

        // Package financials
        $packageRevenue = $data['package_revenue'] ?? $faceValue;
        $packageCost = $rule && $rule->package_cost_override 
            ? $rule->package_cost_override 
            : abs($walletChange);
        $packageProfit = round($packageRevenue - $packageCost, 2);

        // SIM financials
        $simCost = $data['sim_cost'] ?? ($sim ? ($sim->cost_price ?? 0) : 0);
        $simRevenue = $data['sim_revenue'] ?? $simCost; // Default sim revenue = cost (no profit)
        $simProfit = round($simRevenue - $simCost, 2);

        // Totals
        $totalRevenue = round($packageRevenue + $simRevenue, 2);
        $totalCost = round($packageCost + $simCost, 2);
        $totalProfit = round($packageProfit + $simProfit, 2);

        // Wallet balance
        $walletBalanceBefore = $wallet ? $wallet->balance : 0;
        $walletBalanceAfter = round($walletBalanceBefore + $walletChange, 2);

        // Build ledger lines
        $ledgerLines = $this->buildLedgerLines([
            'operator_name' => $operatorName,
            'sim' => $sim,
            'rule' => $rule,
            'face_value' => $faceValue,
            'seller_discount_total' => $sellerDiscountTotal,
            'extra_benefit' => $extraBenefit,
            'wallet_change' => $walletChange,
            'wallet_credit' => $walletCredit,
            'package_profit' => $packageProfit,
            'sim_profit' => $simProfit,
            'total_profit' => $totalProfit,
        ]);

        return [
            'operator_name' => $operatorName,
            'sim' => $sim,
            'wallet' => $wallet,
            'rule' => $rule,
            'package_face_value' => $faceValue,
            'package_revenue' => $packageRevenue,
            'package_cost' => $packageCost,
            'package_profit' => $packageProfit,
            'sim_cost' => $simCost,
            'sim_revenue' => $simRevenue,
            'sim_profit' => $simProfit,
            'seller_discount_total' => $sellerDiscountTotal,
            'seller_extra_benefit' => $extraBenefit,
            'wallet_change' => $walletChange,
            'wallet_balance_before' => $walletBalanceBefore,
            'wallet_balance_after' => $walletBalanceAfter,
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'customer_payment' => $totalRevenue, // What customer pays = package price + SIM price
            'matched_rule_description' => $rule ? $rule->rule_description : 'No matching rule - full deduction',
            'ledger_lines' => $ledgerLines,
        ];
    }

    /**
     * Build human-readable ledger lines
     */
    protected function buildLedgerLines($params)
    {
        $lines = [];
        $sortOrder = 0;

        $operatorName = $params['operator_name'];
        $sim = $params['sim'];
        $rule = $params['rule'];
        $faceValue = $params['face_value'];
        $sellerDiscountTotal = $params['seller_discount_total'];
        $extraBenefit = $params['extra_benefit'];
        $walletChange = $params['wallet_change'];
        $walletCredit = $params['wallet_credit'];
        $packageProfit = $params['package_profit'];
        $simProfit = $params['sim_profit'];
        $totalProfit = $params['total_profit'];

        // Transaction description
        if ($sim) {
            $lines[] = [
                'line_type' => 'info',
                'description' => "SIM Activation: {$operatorName} package (Rs. {$faceValue})",
                'amount' => null,
                'sort_order' => $sortOrder++,
            ];
        } else {
            $lines[] = [
                'line_type' => 'info',
                'description' => "Reload: {$operatorName} (Rs. {$faceValue})",
                'amount' => null,
                'sort_order' => $sortOrder++,
            ];
        }

        // Rule matched
        if ($rule) {
            $lines[] = [
                'line_type' => 'info',
                'description' => "Matched Rule: {$rule->rule_description}",
                'amount' => null,
                'sort_order' => $sortOrder++,
            ];
        }

        // Seller discount
        if ($sellerDiscountTotal > 0) {
            $lines[] = [
                'line_type' => 'discount',
                'description' => "Seller Discount: Rs. {$sellerDiscountTotal}",
                'amount' => $sellerDiscountTotal,
                'sort_order' => $sortOrder++,
            ];
        }

        // Extra benefit
        if ($extraBenefit > 0) {
            $lines[] = [
                'line_type' => 'discount',
                'description' => "Extra Benefit: Rs. {$extraBenefit}",
                'amount' => $extraBenefit,
                'sort_order' => $sortOrder++,
            ];
        }

        // Wallet change
        if ($walletChange > 0) {
            $lines[] = [
                'line_type' => 'wallet_change',
                'description' => "Wallet CREDIT: +Rs. {$walletChange} ({$operatorName})",
                'amount' => $walletChange,
                'sort_order' => $sortOrder++,
            ];
        } elseif ($walletChange < 0) {
            $deduction = abs($walletChange);
            $lines[] = [
                'line_type' => 'wallet_change',
                'description' => "Wallet DEDUCTION: -Rs. {$deduction} ({$operatorName})",
                'amount' => $walletChange,
                'sort_order' => $sortOrder++,
            ];
        } else {
            $lines[] = [
                'line_type' => 'wallet_change',
                'description' => "Wallet CHANGE: Rs. 0.00 (No deduction)",
                'amount' => 0,
                'sort_order' => $sortOrder++,
            ];
        }

        // Profits
        if ($packageProfit != 0) {
            $lines[] = [
                'line_type' => 'profit',
                'description' => "Package Profit: Rs. " . number_format($packageProfit, 2),
                'amount' => $packageProfit,
                'sort_order' => $sortOrder++,
            ];
        }

        if ($sim && $simProfit != 0) {
            $lines[] = [
                'line_type' => 'profit',
                'description' => "SIM Profit: Rs. " . number_format($simProfit, 2),
                'amount' => $simProfit,
                'sort_order' => $sortOrder++,
            ];
        }

        $lines[] = [
            'line_type' => 'profit',
            'description' => "Total Profit: Rs. " . number_format($totalProfit, 2),
            'amount' => $totalProfit,
            'sort_order' => $sortOrder++,
        ];

        return $lines;
    }
}
