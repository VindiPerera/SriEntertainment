<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Report;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\BindingService;
use App\Models\BindingServiceRawMaterial;
use App\Models\RefillBinding;
use App\Models\LaminatingService;
use App\Models\LaminatingServiceRawMaterial;
use App\Models\RefillLaminating;
use App\Models\PhotocopyService;
use App\Models\PhotocopyServiceRawMaterial;
use App\Models\RefillPhotocopy;
use App\Models\PrintoutService;
use App\Models\PrintoutServiceRawMaterial;
use App\Models\RefillPrintout;
use App\Models\SimActivationTransaction;
use App\Models\ReloadSale;
use App\Models\WalletTransaction;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */


       public function index(Request $request)
{
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    $startDate = $request->input('start_date', '');
    $endDate = $request->input('end_date', '');

    // =========================
    // 1. Get Product IDs that were sold within date range
    // =========================
    if ($startDate && $endDate) {
        $productIds = SaleItem::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        })->pluck('product_id')->unique();

        // Only get products involved in sales during the selected date range
        $products = Product::whereIn('id', $productIds)->orderBy('created_at', 'desc')->get();
    } else {
        // If no date filter applied, get all products
        $products = Product::orderBy('created_at', 'desc')->get();
    }

    // =========================
    // 2. Sales Query (with date range if present)
    // =========================
    $salesQuery = Sale::whereHas('saleItems.product.category')
        ->with(['saleItems.product.category', 'employee', 'customer']);

    $salesQuantitiesQuery = SaleItem::query()->whereHas('sale');

    if ($startDate && $endDate) {
        $salesQuery->whereBetween('sale_date', [$startDate, $endDate]);

        $salesQuantitiesQuery->whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        });
    }

    // =========================
    // 3. Get Total Sales Qty per Product (Filtered)
    // =========================
    $salesQuantities = $salesQuantitiesQuery
        ->select('product_id')
        ->selectRaw('SUM(quantity) as total_sales_qty')
        ->groupBy('product_id')
        ->get()
        ->keyBy('product_id');

    // =========================
    // 4. Assign sales_qty to each product
    // =========================
    $products->transform(function ($product) use ($salesQuantities) {
        $product->sales_qty = $salesQuantities->get($product->id)?->total_sales_qty ?? 0;
        return $product;
    });

    // =========================
    // 5. Get Sales Data
    // =========================
    $sales = $salesQuery->orderBy('sale_date', 'desc')->get();

    // =========================
    // 6. Category Sales
    // =========================
    $categorySales = [];
    foreach ($sales as $sale) {
        foreach ($sale->saleItems as $item) {
            $categoryName = $item->product->category->name ?? 'No Category';
            $categorySales[$categoryName] = ($categorySales[$categoryName] ?? 0) + $sale->total_amount;
        }
    }

    // =========================
    // 7. Payment Method Totals
    // =========================
    $paymentMethodTotals = $sales->groupBy('payment_method')
        ->map(function ($group) {
            return $group->sum('total_amount');
        })->toArray();

    // =========================
    // 8. Employee Sales Summary
    // =========================
    $employeeSalesSummary = [];
    foreach ($sales as $sale) {
        if (!$sale->employee) continue;

        $employeeName = $sale->employee->name;
        if (!isset($employeeSalesSummary[$employeeName])) {
            $employeeSalesSummary[$employeeName] = [
                'Employee Name' => $employeeName,
                'Total Sales Amount' => 0,
            ];
        }

        $netAmount = $sale->total_amount - ($sale->discount ?? 0);
        $employeeSalesSummary[$employeeName]['Total Sales Amount'] += $netAmount;
    }

    // =========================
    // 9. Overall Stats
    // =========================
    $totalSaleAmount = $sales->sum('total_amount');
    $totalCost = $sales->sum('total_cost');
    $totalDiscount = $sales->sum('discount');
    $customeDiscount = $sales->sum('custom_discount');
    $netProfit = $totalSaleAmount - $totalCost - $totalDiscount - $customeDiscount;
    $totalTransactions = $sales->count();
    $averageTransactionValue = $totalTransactions > 0 ? $totalSaleAmount / $totalTransactions : 0;
    $totalCustomer = $salesQuery->distinct('customer_id')->count('customer_id');

    // =========================
    // 10. Return Items Data
    // =========================
    $returnItemsQuery = ReturnItem::with(['product', 'customer', 'sale', 'newspaper']);
    
    if ($startDate && $endDate) {
        $returnItemsQuery->whereBetween('return_date', [$startDate, $endDate]);
    }
    
    $returnItems = $returnItemsQuery->orderBy('return_date', 'desc')->get();
    
    // Calculate return items statistics
    $totalReturnedQuantity = $returnItems->sum('quantity');
    $totalReturnItems = $returnItems->count();
    
    // Group return items by reason
    $returnReasons = $returnItems->groupBy('reason')
        ->map(function ($group) {
            return [
                'count' => $group->count(),
                'quantity' => $group->sum('quantity')
            ];
        })->toArray();

    // =========================
    // 11. Binding Service Data
    // =========================
    $bindingServices = BindingService::with(['rawMaterials.product'])->get();
    
    // Calculate usage statistics for each binding service
    $bindingServices->transform(function ($service) use ($startDate, $endDate) {
        // Count how many times this service was used in sales
        $usageQuery = SaleItem::where('binding_id', $service->id);
        
        if ($startDate && $endDate) {
            $usageQuery->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            });
        }
        
        $service->times_used = $usageQuery->sum('quantity');
        
        // Get raw materials with usage information
        $service->raw_materials = $service->rawMaterials->map(function ($rawMaterial) use ($service) {
            $rawMaterial->quantity_used = $rawMaterial->quantity_per_service * $service->times_used;
            return $rawMaterial;
        });
        
        // Get refill stock information
        $service->refill_stock = RefillBinding::with('product')
            ->where('product_id', $service->id)
            ->orWhereHas('product', function ($query) use ($service) {
                $query->whereIn('id', $service->rawMaterials->pluck('product_id'));
            })
            ->get();
            
        return $service;
    });

    // =========================
    // 12. Laminating Service Data
    // =========================
    $laminatingServices = LaminatingService::with(['rawMaterials.product'])->get();
    
    $laminatingServices->transform(function ($service) use ($startDate, $endDate) {
        // Count how many times this service was used in sales
        $usageQuery = SaleItem::where('laminating_id', $service->id);
        
        if ($startDate && $endDate) {
            $usageQuery->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            });
        }
        
        $service->times_used = $usageQuery->sum('quantity');
        
        // Get raw materials with usage information
        $service->raw_materials = $service->rawMaterials->map(function ($rawMaterial) use ($service) {
            $rawMaterial->quantity_used = $rawMaterial->quantity_per_service * $service->times_used;
            return $rawMaterial;
        });
        
        // Get refill stock information
        $service->refill_stock = RefillLaminating::with('product')
            ->where('product_id', $service->id)
            ->orWhereHas('product', function ($query) use ($service) {
                $query->whereIn('id', $service->rawMaterials->pluck('product_id'));
            })
            ->get();
            
        return $service;
    });

    // =========================
    // 13. Photocopy Service Data
    // =========================
    $photocopyServices = PhotocopyService::with(['rawMaterials.product'])->get();
    
    $photocopyServices->transform(function ($service) use ($startDate, $endDate) {
        // Count how many times this service was used in sales
        $usageQuery = SaleItem::where('photocopy_id', $service->id);
        
        if ($startDate && $endDate) {
            $usageQuery->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            });
        }
        
        $service->times_used = $usageQuery->sum('quantity');
        
        // Get raw materials with usage information
        $service->raw_materials = $service->rawMaterials->map(function ($rawMaterial) use ($service) {
            $rawMaterial->quantity_used = $rawMaterial->quantity_per_service * $service->times_used;
            return $rawMaterial;
        });
        
        // Get refill stock information
        $service->refill_stock = RefillPhotocopy::with('product')
            ->where('product_id', $service->id)
            ->orWhereHas('product', function ($query) use ($service) {
                $query->whereIn('id', $service->rawMaterials->pluck('product_id'));
            })
            ->get();
            
        return $service;
    });

    // =========================
    // 14. Printout Service Data
    // =========================
    $printoutServices = PrintoutService::with(['rawMaterials.product'])->get();
    
    $printoutServices->transform(function ($service) use ($startDate, $endDate) {
        // Count how many times this service was used in sales
        $usageQuery = SaleItem::where('printout_id', $service->id);
        
        if ($startDate && $endDate) {
            $usageQuery->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            });
        }
        
        $service->times_used = $usageQuery->sum('quantity');
        
        // Get raw materials with usage information
        $service->raw_materials = $service->rawMaterials->map(function ($rawMaterial) use ($service) {
            $rawMaterial->quantity_used = $rawMaterial->quantity_per_service * $service->times_used;
            return $rawMaterial;
        });
        
        // Get refill stock information
        $service->refill_stock = RefillPrintout::with('product')
            ->where('product_id', $service->id)
            ->orWhereHas('product', function ($query) use ($service) {
                $query->whereIn('id', $service->rawMaterials->pluck('product_id'));
            })
            ->get();
            
        return $service;
    });

    // =========================
    // 15. Get SIM Activation Transactions
    // =========================
    $simActivationQuery = SimActivationTransaction::with('user');
    
    if ($startDate && $endDate) {
        $simActivationQuery->whereBetween('transaction_date', [$startDate, $endDate]);
    }
    
    $simActivationTransactions = $simActivationQuery->orderBy('transaction_date', 'desc')->get();
    
    $totalSimActivationRevenue = $simActivationTransactions->sum('total_revenue');
    $totalSimActivationCost = $simActivationTransactions->sum('total_cost');
    $totalSimActivationProfit = $simActivationTransactions->sum('total_profit');

    // =========================
    // 16. Get Reload Sales
    // =========================
    $reloadSalesQuery = ReloadSale::with('user', 'operator');
    
    if ($startDate && $endDate) {
        $reloadSalesQuery->whereBetween('sale_date', [$startDate, $endDate]);
    }
    
    $reloadSales = $reloadSalesQuery->orderBy('sale_date', 'desc')->get();
    
    $totalReloadRevenue = $reloadSales->where('status', 'completed')->sum('face_value');
    $totalReloadCost = $reloadSales->where('status', 'completed')->sum('net_cost');
    $totalReloadProfit = $totalReloadRevenue - $totalReloadCost;

    // =========================
    // 17. Get Deposit Bonuses (Seller Profit)
    // =========================
    $depositBonusQuery = WalletTransaction::with(['walletAccount.operator', 'walletAccount.user'])
        ->where('transaction_type', 'deposit');
    
    if ($startDate && $endDate) {
        $depositBonusQuery->whereBetween('transaction_date', [$startDate, $endDate]);
    }
    
    $depositBonusTransactions = $depositBonusQuery->orderBy('transaction_date', 'desc')->get();
    $totalDepositBonusProfit = $depositBonusTransactions->sum('bonus_amount');

    // =========================
    // 18. Expenses Summary
    // =========================
    $expenseQuery = Expense::query();

    if ($startDate && $endDate) {
        $expenseQuery->whereBetween('date', [$startDate, $endDate]);
    }

    $expenses = $expenseQuery->orderBy('date', 'desc')->get();
    $totalExpenseAmount = $expenses->sum('amount');
    
    // Group expenses by category for summary
    $expensesByCategory = $expenses->groupBy('category')->map(function ($categoryExpenses) {
        return [
            'total_amount' => $categoryExpenses->sum('amount'),
            'count' => $categoryExpenses->count(),
            'expenses' => $categoryExpenses
        ];
    });

    // =========================
    // 19. Return to Vue via Inertia
    // =========================
    return Inertia::render('Reports/Index', [
        'products' => $products,
        'sales' => $sales,
        'totalSaleAmount' => $totalSaleAmount,
        'totalCustomer' => $totalCustomer,
        'netProfit' => $netProfit,
        'totalDiscount' => $totalDiscount,
        'customeDiscount' => $customeDiscount,
        'totalTransactions' => $totalTransactions,
        'averageTransactionValue' => round($averageTransactionValue, 2),
        'startDate' => $startDate,
        'endDate' => $endDate,
        'categorySales' => $categorySales,
        'employeeSalesSummary' => $employeeSalesSummary,
        'returnItems' => $returnItems,
        'totalReturnedQuantity' => $totalReturnedQuantity,
        'totalReturnItems' => $totalReturnItems,
        'returnReasons' => $returnReasons,
        'bindingServices' => $bindingServices,
        'simActivationTransactions' => $simActivationTransactions,
        'totalSimActivationRevenue' => $totalSimActivationRevenue,
        'totalSimActivationCost' => $totalSimActivationCost,
        'totalSimActivationProfit' => $totalSimActivationProfit,
        'reloadSales' => $reloadSales,
        'totalReloadRevenue' => $totalReloadRevenue,
        'totalReloadCost' => $totalReloadCost,
        'totalReloadProfit' => $totalReloadProfit,
        'depositBonusTransactions' => $depositBonusTransactions,
        'totalDepositBonusProfit' => $totalDepositBonusProfit,
        'laminatingServices' => $laminatingServices,
        'photocopyServices' => $photocopyServices,
        'printoutServices' => $printoutServices,
        'expenses' => $expenses,
        'totalExpenseAmount' => $totalExpenseAmount,
        'expensesByCategory' => $expensesByCategory,
    ]);
}

    public function searchByCode(Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json([
                'products' => [],
                'totalQuantity' => 0,
                'remainingQuantity' => 0
            ]);
        }

        $products = Product::where('code', $code)
            ->select([
                'batch_no',
                'total_quantity',
                'stock_quantity',
                'expire_date',
                'purchase_date',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalQuantity = $products->sum('total_quantity');
        $remainingQuantity = $products->sum('stock_quantity');

        return response()->json([
            'products' => $products,
            'totalQuantity' => $totalQuantity,
            'remainingQuantity' => $remainingQuantity
        ]);
    }




















    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
