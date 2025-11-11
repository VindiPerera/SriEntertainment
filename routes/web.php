<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReturnItemController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CompanyInfoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\SizeController;

use App\Http\Controllers\QuotationController;

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\ManualPosController;
use App\Http\Controllers\NewspaperController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PhotocopyServiceController;
use App\Http\Controllers\PrintoutController;
use App\Http\Controllers\BindingController;
use App\Http\Controllers\LaminatingController;
use App\Http\Controllers\RefillPhotocopyController;
use App\Http\Controllers\RefillPrintoutController;
use App\Http\Controllers\RefillBindingController;
use App\Http\Controllers\BindingRefillController;
use App\Http\Controllers\RefillLaminatingController;
use App\Http\Controllers\SimReloadController;
use App\Http\Controllers\SimStockController;
use App\Http\Controllers\MobileTopUpController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ReloadPackageController;
use App\Http\Controllers\OperatorController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });
Route::get('/dashboard', function () {
    return Inertia::location(route('dashboard'));
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        //
        if (Gate::allows('hasRole', ['Cashier'])) {
            return redirect()->route('pos.index');
        }

        return Inertia::render('Dashboard');

    })->name('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::resource('categories', CategoryController::class);
        // SIM Stock Management Routes
    Route::resource('sim-stocks', SimStockController::class);
    
    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::post('suppliers/{supplier}', [SupplierController::class, 'update']);
    Route::post('products/{product}', [ProductController::class, 'update']);
    Route::post('products-variant', [ProductController::class, 'productVariantStore'])->name('productVariant');

    Route::post('products-size', [ProductController::class, 'sizeStore'])->name('productSize');


    // Route::resource('company-info', CompanyInfoController::class)->name('companyInfo.index');
    Route::get('/company-info', [CompanyInfoController::class, 'index'])->name('companyInfo.index');
    Route::post('/company-info/{companyInfo}', [CompanyInfoController::class, 'update'])->name('companyInfo.update');


    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'getProduct'])->name('pos.getProduct');
    Route::post('/get-coupon', [PosController::class, 'getCoupon'])->name('pos.getCoupon');
    Route::post('/pos/submit', [PosController::class, 'submit'])->name('pos.submit');
    Route::get('/api/newspapers', [PosController::class, 'getNewspapers'])->name('pos.getNewspapers');
    Route::get('/api/photocopy-services', [PosController::class, 'getPhotocopyServices'])->name('pos.getPhotocopyServices');
    Route::get('/api/printout-services', [PosController::class, 'getPrintoutServices'])->name('pos.getPrintoutServices');
    Route::get('/api/binding-services', [PosController::class, 'getBindingServices'])->name('pos.getBindingServices');
    Route::get('/api/laminating-services', [PosController::class, 'getLaminatingServices'])->name('pos.getLaminatingServices');
    Route::get('/api/all-products', [PosController::class, 'getAllProducts'])->name('pos.getAllProducts');
    Route::resource('payment', PaymentController::class);
    Route::resource('reports', ReportController::class);
    Route::get('/batch-management/search', [ReportController::class, 'searchByCode']);
    Route::resource('customers', CustomerController::class);
    Route::resource('colors', ColorController::class);
    Route::resource('coupons', CouponController::class);
    Route::resource('sizes', SizeController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('transactionHistory', TransactionHistoryController::class );
    Route::post('/transactions/delete', [TransactionHistoryController::class, 'destroy'])->name('transactions.delete');
    Route::resource('stock-transition', StockTransactionController::class);
    Route::resource('manualpos', ManualPosController::class);



    Route::resource('/quotation', QuotationController::class);
    Route::post('/api/save-quotation', [QuotationController::class, 'saveQuotationPdf']);

    Route::get('/sim-reload', [SimReloadController::class, 'index'])->name('simreload.index');
    
    // Centralized SIM Stock Management - Single Page
    Route::get('/sim-reload/stock', [SimReloadController::class, 'manageSimStock'])->name('simreload.stock');
    
    Route::get('/sim-reload/mobitel', [SimReloadController::class, 'mobitel'])->name('simreload.mobitel');
    Route::get('/sim-reload/mobitel/stock', [SimReloadController::class, 'mobitelStock'])->name('simreload.mobitel.stock');
    Route::get('/sim-reload/mobitel/packages', [SimReloadController::class, 'mobitelPackages'])->name('simreload.mobitel.packages');
    Route::get('/sim-reload/mobitel/activation-packages', [SimReloadController::class, 'mobitelActivationPackages'])->name('simreload.mobitel.activation');
    Route::get('/sim-reload/mobitel/wallet', [SimReloadController::class, 'mobitelWallet'])->name('simreload.mobitel.wallet');
    
    Route::get('/sim-reload/dialog', [SimReloadController::class, 'dialog'])->name('simreload.dialog');
    Route::get('/sim-reload/dialog/stock', [SimReloadController::class, 'dialogStock'])->name('simreload.dialog.stock');
    Route::get('/sim-reload/dialog/packages', [SimReloadController::class, 'dialogPackages'])->name('simreload.dialog.packages');
    Route::get('/sim-reload/dialog/activation-packages', [SimReloadController::class, 'dialogActivationPackages'])->name('simreload.dialog.activation');
    Route::get('/sim-reload/dialog/wallet', [SimReloadController::class, 'dialogWallet'])->name('simreload.dialog.wallet');
    
    Route::get('/sim-reload/airtel', [SimReloadController::class, 'airtel'])->name('simreload.airtel');
    Route::get('/sim-reload/airtel/stock', [SimReloadController::class, 'airtelStock'])->name('simreload.airtel.stock');
    Route::get('/sim-reload/airtel/packages', [SimReloadController::class, 'airtelPackages'])->name('simreload.airtel.packages');
    Route::get('/sim-reload/airtel/activation-packages', [SimReloadController::class, 'airtelActivationPackages'])->name('simreload.airtel.activation');
    Route::get('/sim-reload/airtel/wallet', [SimReloadController::class, 'airtelWallet'])->name('simreload.airtel.wallet');
    
    Route::get('/sim-reload/hutch', [SimReloadController::class, 'hutch'])->name('simreload.hutch');
    Route::get('/sim-reload/hutch/stock', [SimReloadController::class, 'hutchStock'])->name('simreload.hutch.stock');
    Route::get('/sim-reload/hutch/packages', [SimReloadController::class, 'hutchPackages'])->name('simreload.hutch.packages');
    Route::get('/sim-reload/hutch/activation-packages', [SimReloadController::class, 'hutchActivationPackages'])->name('simreload.hutch.activation');
    Route::get('/sim-reload/hutch/wallet', [SimReloadController::class, 'hutchWallet'])->name('simreload.hutch.wallet');
    
    // Mobile Top-Up Routes
    Route::get('/mobile-topup', [MobileTopUpController::class, 'index'])->name('mobile-topup.index');
    Route::get('/mobile-topup/manage-wallet', [MobileTopUpController::class, 'manageWallet'])->name('mobile-topup.manage-wallet');
    Route::get('/mobile-topup/sim-activation-packages', [MobileTopUpController::class, 'simActivationPackages'])->name('mobile-topup.sim-activation-packages');
    Route::get('/mobile-topup/normal-packages', [MobileTopUpController::class, 'normalPackages'])->name('mobile-topup.normal-packages');
    
    // Operator Management Routes
    Route::prefix('operators')->group(function () {
        Route::post('/', [OperatorController::class, 'store']);
        Route::put('/{id}', [OperatorController::class, 'update']);
        Route::delete('/{id}', [OperatorController::class, 'destroy']);
    });
    
    // Wallet API Routes
    Route::prefix('api/wallet')->group(function () {
        Route::get('/', [WalletController::class, 'index']);
        Route::post('/deposit', [WalletController::class, 'deposit']);
        Route::post('/sell', [WalletController::class, 'sell']);
        Route::post('/quote', [WalletController::class, 'quote']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
        Route::post('/transactions/export-pdf', [WalletController::class, 'exportTransactionsPDF']);
        Route::get('/packages', [WalletController::class, 'getPackages']);
    });
    
    // Reload Packages API Routes
    Route::prefix('api/reload-packages')->group(function () {
        Route::get('/', [ReloadPackageController::class, 'index']);
        Route::post('/', [ReloadPackageController::class, 'store']);
        Route::get('/{id}', [ReloadPackageController::class, 'show']);
        Route::put('/{id}', [ReloadPackageController::class, 'update']);
        Route::delete('/{id}', [ReloadPackageController::class, 'destroy']);
        Route::get('/operator/{operatorId}', [ReloadPackageController::class, 'byOperator']);
    });
    
    
    Route::get('/products/{id}/promotion-items', [ProductController::class, 'getPromotionItems']);


    // Route::get('/stock-transition', [PosController::class, 'index'])->name('pos.index');
    // Route::post('/stock-transition', [PosController::class, 'getProduct'])->name('pos.getProduct');
  Route::post('/api/products2', [ProductController::class, 'fetchProducts2']);

    Route::resource('return-bill', ReturnItemController::class);




    Route::post('/api/products', [BindingRefillController::class, 'fetchProducts']);
    Route::post('/api/sale/items', [ReturnItemController::class, 'fetchSaleItems'])->name('sale.items');

    Route::get('/services/photocopy', function () {
        return Inertia::render('Services/PhotocopyServicePage');
    });

    Route::get('/services/laminating', function () {
        return Inertia::render('Services/LaminatingServicePage');
    });

    Route::get('/services/binding', function () {
        return Inertia::render('Services/BindingServicePage');
    });

    Route::get('/services/printout', function () {
        return Inertia::render('Services/PrintoutServicePage');
    });
    
    Route::get('/newspapers/batch', [NewspaperController::class, 'getNextBatchNumber']);
    Route::resource('newspapers', NewspaperController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('photocopy-services', PhotocopyServiceController::class);
    Route::resource('printout-services', PrintoutController::class);
    Route::resource('binding-services', BindingController::class);
    Route::resource('laminating-services', LaminatingController::class);

    // Refill Photocopy Routes
    Route::get('/refillphotocopy', [RefillPhotocopyController::class, 'index'])->name('refillphotocopy.index');
    Route::post('/refillphotocopy', [RefillPhotocopyController::class, 'store'])->name('refillphotocopy.store');
    Route::post('/api/refill-photocopy', [RefillPhotocopyController::class, 'store']);
    // API: Get all products with stock 0 (for notification)
    Route::get('/api/photocopy/low-stock', [RefillPhotocopyController::class, 'lowStockProducts']);
    Route::get('/api/printout/low-stock', [RefillPrintoutController::class, 'lowStockProducts']);
    Route::get('/api/binding/low-stock', [RefillBindingController::class, 'lowStockProducts']);
    Route::get('/api/laminating/low-stock', [RefillLaminatingController::class, 'lowStockProducts']);
    Route::post('/api/refill-printout', [RefillPrintoutController::class, 'store']);

    // Add this route to your web.php file
Route::get('/printout-services', [PrintoutController::class, 'index'])->name('printout-services.index');
Route::get('/binding-services', [BindingController::class, 'index'])->name('binding-services.index');
    
    Route::post('/api/refill-binding', [BindingRefillController::class, 'store']);
    Route::post('/api/refill-binding-by-code', [BindingRefillController::class, 'storeByCode']);

    // Add routes for Refill Laminating
    Route::get('/refilllaminating', [RefillLaminatingController::class, 'index'])->name('refilllaminating.index');
    Route::post('/api/refill-laminating', [RefillLaminatingController::class, 'store']);
    Route::post('/api/refill-laminating-by-code', [RefillLaminatingController::class, 'storeByCode']);

    // API routes for services
    Route::get('/api/categories', [PhotocopyServiceController::class, 'fetchCategories']);
    Route::get('/api/products', [PhotocopyServiceController::class, 'fetchProducts']);
    
    // Laminating service API routes
    Route::get('/api/laminating/categories', [LaminatingController::class, 'fetchCategories']);
    Route::get('/api/laminating/products', [LaminatingController::class, 'fetchProducts']);

    Route::get('/api/binding/products', [BindingController::class, 'fetchProducts']);
    
    Route::get('/api/photocopy/products', [PhotocopyServiceController::class, 'fetchProducts']);
    
    Route::get('/api/printout/products', [PrintoutController::class, 'fetchProducts']);
});




