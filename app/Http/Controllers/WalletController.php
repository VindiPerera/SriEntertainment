<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\WalletAccount;
use App\Models\WalletTransaction;
use App\Models\ReloadSale;
use App\Models\ReloadPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class WalletController extends Controller
{
    /**
     * Display wallet management page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all operators with their wallet accounts for this user
        $operators = Operator::active()
            ->with(['walletAccounts' => function($query) use ($user) {
                $query->where('user_id', $user->id)->active();
            }])
            ->get();

        // Get wallet balances
        $wallets = WalletAccount::where('user_id', $user->id)
            ->with('operator')
            ->active()
            ->get();

        // Get recent transactions
        $recentTransactions = WalletTransaction::whereIn('wallet_account_id', $wallets->pluck('id'))
            ->with(['walletAccount.operator', 'performedBy'])
            ->orderBy('transaction_date', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('SimReload/ManageWallet', [
            'operators' => $operators,
            'wallets' => $wallets,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Deposit money to wallet
     * For Airtel/Hutch: adds deposit + bonus
     * For Mobitel/Dialog: adds deposit only
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'operator_id' => 'required|exists:operators,id',
            'amount' => 'required|numeric|min:1|max:1000000',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $user = Auth::user();
                $operator = Operator::findOrFail($request->operator_id);
                $amount = $request->amount;

                // Get or create wallet account
                $wallet = WalletAccount::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'operator_id' => $operator->id,
                    ],
                    [
                        'balance' => 0,
                        'total_deposits' => 0,
                        'total_sales' => 0,
                        'total_commissions' => 0,
                        'is_active' => true,
                    ]
                );

                // Lock wallet for update
                $wallet = WalletAccount::where('id', $wallet->id)->lockForUpdate()->first();
                
                $balanceBefore = $wallet->balance;
                $bonusAmount = 0;
                $percentage = 0;
                $creditAmount = $amount;

                // Calculate bonus for Airtel/Hutch (deposit_bonus model)
                if ($operator->usesDepositBonus()) {
                    $percentage = $operator->getEffectiveRate($user->id, 'deposit_bonus');
                    $bonusAmount = ($amount * $percentage) / 100;
                    $creditAmount = $amount + $bonusAmount;
                }

                // Update wallet balance
                $wallet->balance = $wallet->balance + $creditAmount;
                $wallet->total_deposits = $wallet->total_deposits + $amount;
                $wallet->save();

                // Create transaction record
                $transaction = WalletTransaction::create([
                    'wallet_account_id' => $wallet->id,
                    'transaction_type' => 'deposit',
                    'face_amount' => $amount,
                    'percentage_applied' => $percentage,
                    'bonus_amount' => $bonusAmount,
                    'commission_amount' => 0,
                    'debit' => 0,
                    'credit' => $creditAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'reference' => 'DEP-' . strtoupper(uniqid()),
                    'notes' => $request->notes,
                    'performed_by' => $user->id,
                    'transaction_date' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $bonusAmount > 0 
                        ? "Deposit successful! Rs. {$amount} + Rs. {$bonusAmount} bonus added to {$operator->name} wallet."
                        : "Deposit successful! Rs. {$amount} added to {$operator->name} wallet.",
                    'wallet' => $wallet->fresh(),
                    'transaction' => $transaction,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sell a reload package
     * For Mobitel/Dialog: applies sale commission
     * For Airtel/Hutch: no commission by default (unless configured)
     */
    public function sell(Request $request)
    {
        $request->validate([
            'operator_id' => 'required|exists:operators,id',
            'reload_package_id' => 'required|exists:reload_packages,id',
            'msisdn' => 'required|regex:/^[0-9]{10}$/',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $user = Auth::user();
                $operator = Operator::findOrFail($request->operator_id);
                $package = ReloadPackage::findOrFail($request->reload_package_id);
                $msisdn = $request->msisdn;

                // Get wallet account
                $wallet = WalletAccount::where('user_id', $user->id)
                    ->where('operator_id', $operator->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$wallet->is_active) {
                    throw new \Exception('Wallet is inactive');
                }

                $faceValue = $package->face_value;
                $commissionPercent = 0;
                $commissionAmount = 0;
                $netCost = $faceValue;

                // Calculate commission for Mobitel/Dialog (sale_commission model)
                if ($operator->usesSaleCommission()) {
                    $commissionPercent = $operator->getEffectiveRate($user->id, 'sale_commission');
                    $commissionAmount = ($faceValue * $commissionPercent) / 100;
                    $netCost = $faceValue - $commissionAmount;
                }

                // Check sufficient balance
                if (!$wallet->hasSufficientBalance($netCost)) {
                    throw new \Exception('Insufficient wallet balance. Required: Rs. ' . number_format($netCost, 2));
                }

                $balanceBefore = $wallet->balance;

                // Update wallet balance
                $wallet->balance = $wallet->balance - $netCost;
                $wallet->total_sales = $wallet->total_sales + $faceValue;
                $wallet->total_commissions = $wallet->total_commissions + $commissionAmount;
                $wallet->save();

                // Create reload sale record
                $reloadSale = ReloadSale::create([
                    'user_id' => $user->id,
                    'operator_id' => $operator->id,
                    'wallet_account_id' => $wallet->id,
                    'reload_package_id' => $package->id,
                    'msisdn' => $msisdn,
                    'face_value' => $faceValue,
                    'commission_percent' => $commissionPercent,
                    'commission_amount' => $commissionAmount,
                    'net_cost' => $netCost,
                    'wallet_balance_before' => $balanceBefore,
                    'wallet_balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'transaction_reference' => 'RLD-' . strtoupper(uniqid()),
                    'notes' => $request->notes,
                    'sale_date' => now(),
                ]);

                // Create wallet transaction
                $transaction = WalletTransaction::create([
                    'wallet_account_id' => $wallet->id,
                    'transaction_type' => 'sale',
                    'face_amount' => $faceValue,
                    'percentage_applied' => $commissionPercent,
                    'bonus_amount' => 0,
                    'commission_amount' => $commissionAmount,
                    'debit' => $netCost,
                    'credit' => 0,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'reference' => $reloadSale->transaction_reference,
                    'notes' => "Reload sale to {$msisdn}",
                    'performed_by' => $user->id,
                    'reload_sale_id' => $reloadSale->id,
                    'transaction_date' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Reload sale successful! Rs. {$faceValue} to {$msisdn}",
                    'reloadSale' => $reloadSale,
                    'wallet' => $wallet->fresh(),
                    'commission' => $commissionAmount,
                    'netCost' => $netCost,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sale failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview/quote a reload sale before confirming
     */
    public function quote(Request $request)
    {
        $request->validate([
            'operator_id' => 'required|exists:operators,id',
            'reload_package_id' => 'required|exists:reload_packages,id',
        ]);

        try {
            $user = Auth::user();
            $operator = Operator::findOrFail($request->operator_id);
            $package = ReloadPackage::findOrFail($request->reload_package_id);

            // Get wallet account
            $wallet = WalletAccount::where('user_id', $user->id)
                ->where('operator_id', $operator->id)
                ->first();

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'No wallet found for this operator. Please deposit first.',
                ], 404);
            }

            $faceValue = $package->face_value;
            $commissionPercent = 0;
            $commissionAmount = 0;
            $netCost = $faceValue;

            // Calculate commission
            if ($operator->usesSaleCommission()) {
                $commissionPercent = $operator->getEffectiveRate($user->id, 'sale_commission');
                $commissionAmount = ($faceValue * $commissionPercent) / 100;
                $netCost = $faceValue - $commissionAmount;
            }

            $sufficientBalance = $wallet->hasSufficientBalance($netCost);

            return response()->json([
                'success' => true,
                'quote' => [
                    'operator' => $operator->name,
                    'package' => $package->name,
                    'face_value' => $faceValue,
                    'commission_percent' => $commissionPercent,
                    'commission_amount' => $commissionAmount,
                    'net_cost' => $netCost,
                    'current_balance' => $wallet->balance,
                    'balance_after' => $wallet->balance - $netCost,
                    'sufficient_balance' => $sufficientBalance,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quote failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get reload packages for an operator
     */
    public function getPackages(Request $request)
    {
        $operatorId = $request->input('operator_id');
        
        if (!$operatorId) {
            return response()->json([
                'success' => false,
                'message' => 'Operator ID is required',
                'packages' => [],
            ], 400);
        }
        
        $packages = ReloadPackage::forOperator($operatorId)
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'packages' => $packages,
        ]);
    }

    /**
     * Get wallet transactions for reporting
     */
    public function transactions(Request $request)
    {
        $request->validate([
            'operator_id' => 'nullable|exists:operators,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'transaction_type' => 'nullable|in:deposit,sale,adjustment,refund',
        ]);

        $user = Auth::user();
        
        $wallets = WalletAccount::where('user_id', $user->id)
            ->when($request->operator_id, function($q) use ($request) {
                $q->where('operator_id', $request->operator_id);
            })
            ->pluck('id');

        $transactions = WalletTransaction::whereIn('wallet_account_id', $wallets)
            ->with(['walletAccount.operator', 'performedBy', 'reloadSale'])
            ->when($request->from_date && $request->to_date, function($q) use ($request) {
                $q->dateRange($request->from_date, $request->to_date);
            })
            ->when($request->transaction_type, function($q) use ($request) {
                $q->where('transaction_type', $request->transaction_type);
            })
            ->orderBy('transaction_date', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Export wallet transactions to PDF
     */
    public function exportTransactionsPDF(Request $request)
    {
        $request->validate([
            'operator_id' => 'nullable|exists:operators,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'type' => 'nullable|in:deposit,sale,adjustment,refund',
        ]);

        $user = Auth::user();
        
        $wallets = WalletAccount::where('user_id', $user->id)
            ->when($request->operator_id, function($q) use ($request) {
                $q->where('operator_id', $request->operator_id);
            })
            ->pluck('id');

        $transactions = WalletTransaction::whereIn('wallet_account_id', $wallets)
            ->with(['walletAccount.operator', 'performedBy'])
            ->when($request->from_date, function($q) use ($request) {
                $q->whereDate('transaction_date', '>=', $request->from_date);
            })
            ->when($request->to_date, function($q) use ($request) {
                $q->whereDate('transaction_date', '<=', $request->to_date);
            })
            ->when($request->type, function($q) use ($request) {
                $q->where('transaction_type', $request->type);
            })
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('pdfs.wallet-transactions', [
            'transactions' => $transactions,
            'user' => $user,
            'filters' => [
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'operator_id' => $request->operator_id,
                'type' => $request->type,
            ]
        ]);

        return $pdf->download('wallet-transactions-' . date('Y-m-d-His') . '.pdf');
    }
}

