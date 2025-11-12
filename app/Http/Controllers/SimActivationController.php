<?php

namespace App\Http\Controllers;

use App\Models\SimActivationTransaction;
use App\Models\TransactionLedgerLine;
use App\Models\Operator;
use App\Models\ReloadPackage;
use App\Models\SimStock;
use App\Models\WalletAccount;
use App\Services\SimActivationRuleEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SimActivationController extends Controller
{
    protected $ruleEngine;

    public function __construct(SimActivationRuleEngine $ruleEngine)
    {
        $this->ruleEngine = $ruleEngine;
    }

    /**
     * Show SIM activation page
     */
    public function index()
    {
        // Get available SIM stocks (where stock > 0)
        $simStocks = SimStock::where('stock', '>', 0)
            ->get()
            ->map(function($sim) {
                // Map sim_name to operator for consistency
                $sim->operator_name = $sim->sim_name;
                return $sim;
            });

        $user = Auth::user();
        $wallets = WalletAccount::where('user_id', $user->id)
            ->with('operator')
            ->where('is_active', true)
            ->get()
            ->keyBy('operator_id');

        return Inertia::render('SimActivation/Index', [
            'simStocks' => $simStocks,
            'wallets' => $wallets,
        ]);
    }

    /**
     * Process SIM activation transaction
     */
    public function store(Request $request)
    {
        $request->validate([
            'operator_name' => 'required|string',
            'sim_stock_id' => 'nullable|exists:sim_stocks,id',
            'pricing_rule_id' => 'required|exists:operator_pricing_rules,id',
            'mobile_number' => 'required|string|max:15|unique:sim_activation_transactions,mobile_number',
            'package_revenue' => 'nullable|numeric|min:0',
            'sim_cost' => 'nullable|numeric|min:0',
            'sim_revenue' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $user = Auth::user();
                
                // Get the pricing rule to get face_value
                $pricingRule = \App\Models\OperatorPricingRule::findOrFail($request->pricing_rule_id);
                
                // Prepare data for rule engine
                $calculationData = [
                    'operator_name' => $request->operator_name,
                    'pricing_rule_id' => $request->pricing_rule_id,
                    'face_value' => $pricingRule->face_value,
                    'sim_stock_id' => $request->sim_stock_id,
                    'user_id' => $user->id,
                    'package_revenue' => $request->package_revenue,
                    'sim_cost' => $request->sim_cost,
                    'sim_revenue' => $request->sim_revenue,
                    'transaction_type' => 'sim_activation',
                ];

                // Calculate transaction using rule engine
                $calc = $this->ruleEngine->calculateTransaction($calculationData);

                // Check wallet balance if deduction is required
                if ($calc['wallet']) {
                    // If wallet_change is negative (deduction), check if sufficient balance
                    if ($calc['wallet_change'] < 0) {
                        $requiredBalance = abs($calc['wallet_change']);
                        if ($calc['wallet']->balance < $requiredBalance) {
                            return response()->json([
                                'success' => false,
                                'message' => "Insufficient wallet balance. Required: Rs. " . number_format($requiredBalance, 2) . 
                                           ", Available: Rs. " . number_format($calc['wallet']->balance, 2),
                            ], 400);
                        }
                    }
                    
                    // Update wallet balance
                    $calc['wallet']->balance = $calc['wallet_balance_after'];
                    $calc['wallet']->save();
                } else {
                    // No wallet found - check if deduction is required
                    if ($calc['wallet_change'] < 0) {
                        return response()->json([
                            'success' => false,
                            'message' => "No wallet account found for {$request->operator_name}. Please create a wallet account first.",
                        ], 400);
                    }
                }

                // Create transaction record
                $transaction = SimActivationTransaction::create([
                    'transaction_number' => SimActivationTransaction::generateTransactionNumber(),
                    'operator_name' => $request->operator_name,
                    'sim_stock_id' => $request->sim_stock_id,
                    'user_id' => $user->id,
                    'wallet_account_id' => $calc['wallet'] ? $calc['wallet']->id : null,
                    'pricing_rule_id' => $request->pricing_rule_id,
                    'transaction_type' => 'sim_activation',
                    'mobile_number' => $request->mobile_number,
                    'package_face_value' => $calc['package_face_value'],
                    'package_revenue' => $calc['package_revenue'],
                    'package_cost' => $calc['package_cost'],
                    'package_profit' => $calc['package_profit'],
                    'sim_cost' => $calc['sim_cost'],
                    'sim_revenue' => $calc['sim_revenue'],
                    'sim_profit' => $calc['sim_profit'],
                    'seller_discount_total' => $calc['seller_discount_total'],
                    'seller_extra_benefit' => $calc['seller_extra_benefit'],
                    'wallet_change' => $calc['wallet_change'],
                    'wallet_balance_before' => $calc['wallet_balance_before'],
                    'wallet_balance_after' => $calc['wallet_balance_after'],
                    'total_profit' => $calc['total_profit'],
                    'total_revenue' => $calc['total_revenue'],
                    'total_cost' => $calc['total_cost'],
                    'matched_rule_description' => $calc['matched_rule_description'],
                    'notes' => $request->notes,
                    'transaction_date' => now(),
                    'payment_method' => $request->payment_method ?? 'cash',
                    'card_surcharge' => $request->card_surcharge ?? 0,
                    'cash_received' => $request->cash_received ?? 0,
                    'change_amount' => $request->change_amount ?? 0,
                ]);

                // Create ledger lines
                foreach ($calc['ledger_lines'] as $line) {
                    TransactionLedgerLine::create([
                        'sim_activation_transaction_id' => $transaction->id,
                        'line_type' => $line['line_type'],
                        'description' => $line['description'],
                        'amount' => $line['amount'],
                        'sort_order' => $line['sort_order'],
                    ]);
                }

                // Update SIM stock quantity if applicable
                if ($request->sim_stock_id) {
                    $sim = SimStock::find($request->sim_stock_id);
                    if ($sim && $sim->stock > 0) {
                        $previousStock = $sim->stock;
                        
                        // Decrease stock quantity by 1
                        $sim->stock = $sim->stock - 1;
                        $sim->save();
                        
                        // Create stock movement record
                        \App\Models\SimStockMovement::create([
                            'sim_stock_id' => $sim->id,
                            'movement_type' => 'out',
                            'quantity' => -1,
                            'previous_stock' => $previousStock,
                            'current_stock' => $sim->stock,
                            'reference_type' => 'sim_activation',
                            'reference_id' => $transaction->id,
                            'notes' => 'SIM sold via activation transaction',
                            'movement_date' => now(),
                        ]);
                    }
                }

                // Load relationships for response
                $transaction->load(['ledgerLines', 'simStock', 'pricingRule']);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaction processed successfully',
                    'transaction' => $transaction,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transaction preview (quote)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'operator_name' => 'required|string',
            'sim_stock_id' => 'nullable|exists:sim_stocks,id',
            'pricing_rule_id' => 'required|exists:operator_pricing_rules,id',
            'package_revenue' => 'nullable|numeric|min:0',
            'sim_cost' => 'nullable|numeric|min:0',
            'sim_revenue' => 'nullable|numeric|min:0',
        ]);

        try {
            $user = Auth::user();
            
            // Get the pricing rule to get face_value
            $pricingRule = \App\Models\OperatorPricingRule::findOrFail($request->pricing_rule_id);
            
            $calculationData = [
                'operator_name' => $request->operator_name,
                'pricing_rule_id' => $request->pricing_rule_id,
                'face_value' => $pricingRule->face_value,
                'sim_stock_id' => $request->sim_stock_id,
                'user_id' => $user->id,
                'package_revenue' => $request->package_revenue,
                'sim_cost' => $request->sim_cost,
                'sim_revenue' => $request->sim_revenue,
                'transaction_type' => 'sim_activation',
            ];

            $calc = $this->ruleEngine->calculateTransaction($calculationData);

            return response()->json([
                'success' => true,
                'preview' => $calc,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transaction history with filters
     */
    public function history(Request $request)
    {
        $query = SimActivationTransaction::with(['simStock', 'user', 'ledgerLines', 'pricingRule'])
            ->orderBy('transaction_date', 'desc');

        // Apply filters
        if ($request->operator_name) {
            $query->where('operator_name', $request->operator_name);
        }

        if ($request->transaction_type) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('transaction_date', [$request->from_date, $request->to_date]);
        }

        if ($request->face_value) {
            $query->where('package_face_value', $request->face_value);
        }

        $transactions = $query->paginate(50);

        return response()->json($transactions);
    }

    /**
     * Show analytics page
     */
    public function analytics()
    {
        $operators = Operator::where('is_active', true)->get();
        
        return Inertia::render('SimActivation/Analytics', [
            'operators' => $operators,
        ]);
    }

    /**
     * Get analytics data
     */
    public function analyticsData(Request $request)
    {
        $query = SimActivationTransaction::query();

        // Apply filters
        if ($request->operator_id) {
            $query->where('operator_id', $request->operator_id);
        }

        if ($request->transaction_type) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('transaction_date', [$request->from_date, $request->to_date]);
        }

        if ($request->face_value) {
            $query->where('package_face_value', $request->face_value);
        }

        // Aggregate data
        $summary = [
            'total_transactions' => $query->count(),
            'total_revenue' => $query->sum('total_revenue'),
            'total_cost' => $query->sum('total_cost'),
            'total_profit' => $query->sum('total_profit'),
            'package_profit' => $query->sum('package_profit'),
            'sim_profit' => $query->sum('sim_profit'),
            'total_wallet_change' => $query->sum('wallet_change'),
        ];

        // Group by operator
        $byOperator = SimActivationTransaction::selectRaw('
                operator_id,
                COUNT(*) as transaction_count,
                SUM(total_revenue) as total_revenue,
                SUM(total_profit) as total_profit,
                SUM(package_profit) as package_profit,
                SUM(sim_profit) as sim_profit,
                SUM(wallet_change) as wallet_change
            ')
            ->when($request->from_date && $request->to_date, function($q) use ($request) {
                $q->whereBetween('transaction_date', [$request->from_date, $request->to_date]);
            })
            ->groupBy('operator_id')
            ->with('operator')
            ->get();

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'by_operator' => $byOperator,
        ]);
    }
}
