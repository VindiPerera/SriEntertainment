<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\WalletAccount;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MobileTopUpController extends Controller
{
    /**
     * Display the mobile top-up dashboard
     */
    public function index()
    {
        return Inertia::render('MobileTopUp/Index', [
            'pageTitle' => 'Mobile Top-Up Services'
        ]);
    }

    /**
     * Show the manage wallet page
     */
    public function manageWallet()
    {
        $user = Auth::user();
        
        // Get all operators with their wallet accounts and rates for this user
        $operators = Operator::where('is_active', true)
            ->with([
                'walletAccounts' => function($query) use ($user) {
                    $query->where('user_id', $user->id)->where('is_active', true);
                },
                'rates' => function($query) {
                    $query->where('is_active', true)->orderBy('percentage', 'asc');
                }
            ])
            ->get();

        // Get wallet balances
        $wallets = WalletAccount::where('user_id', $user->id)
            ->with('operator')
            ->where('is_active', true)
            ->get();

        // Get recent transactions
        $recentTransactions = [];
        if ($wallets->count() > 0) {
            $recentTransactions = \App\Models\WalletTransaction::whereIn('wallet_account_id', $wallets->pluck('id'))
                ->with(['walletAccount.operator', 'performedBy'])
                ->orderBy('transaction_date', 'desc')
                ->limit(20)
                ->get();
        }

        return Inertia::render('SimReload/ManageWallet', [
            'pageTitle' => 'Manage Wallet',
            'operators' => $operators,
            'wallets' => $wallets,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Show the SIM activation packages page
     */
    public function simActivationPackages()
    {
        // Redirect to the new SimActivation module
        return redirect()->route('sim-activation.index');
    }

    /**
     * Show the normal packages page
     */
    public function normalPackages()
    {
        $user = Auth::user();
        $operators = Operator::where('is_active', true)
            ->with(['reloadPackages' => function($query) {
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('face_value');
            }])
            ->get();
        
        // Get wallets for balance display
        $wallets = WalletAccount::where('user_id', $user->id)
            ->with('operator')
            ->where('is_active', true)
            ->get()
            ->keyBy('operator_id');

        return Inertia::render('SimReload/NormalPackages', [
            'operators' => $operators,
            'wallets' => $wallets,
        ]);
    }
}