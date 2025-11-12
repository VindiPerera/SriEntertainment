<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Size;
use App\Models\StockTransaction;
use App\Models\Employee;
use App\Models\Newspaper;
use App\Models\PhotocopyService;
use App\Models\RefillPhotocopy;
use App\Models\PhotocopyServiceRawMaterial;
use App\Models\PrintoutService;
use App\Models\RefillPrintout;
use App\Models\PrintoutServiceRawMaterial;
use App\Models\LaminatingService;
use App\Models\RefillLaminating;
use App\Models\LaminatingServiceRawMaterial;
use App\Models\BindingService;
use App\Models\RefillBinding;
use App\Models\BindingRefill;
use App\Models\BindingServiceRawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->parent ? $category->parent->name : null;
            return $category;
        });
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $allemployee = Employee::orderBy('created_at', 'desc')->get();

        return Inertia::render('Pos/Index', [
            'product' => null,
            'error' => null,
            'loggedInUser' => Auth::user(),
            'allcategories' => $allcategories,
            'allemployee' => $allemployee,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    public function getProduct(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'barcode' => 'required',
        ]);

        // First try to find in products
        $product = Product::where('barcode', $request->barcode)
            ->orWhere('code', $request->barcode)
            ->first();

        if ($product) {
            return response()->json([
                'product' => $product,
                'type' => 'product',
                'error' => null,
            ]);
        }

        // If not found in products, try newspapers
        $newspaper = Newspaper::where('barcode', $request->barcode)
            ->orWhere('productcode', $request->barcode)
            ->first();

        if ($newspaper) {
            // Format newspaper to match product structure
            return response()->json([
                'product' => [
                    'id' => $newspaper->id,
                    'name' => $newspaper->name,
                    'barcode' => $newspaper->barcode,
                    'selling_price' => $newspaper->selling_price,
                    'cost_price' => $newspaper->cost_price ?? 0,
                    'stock_quantity' => $newspaper->stock_quantity,
                    'discount' => $newspaper->discount ?? 0,
                    'discounted_price' => $newspaper->discount_price ?? $newspaper->selling_price,
                    'is_newspaper' => true,
                    'image' => null,
                ],
                'type' => 'newspaper',
                'error' => null,
            ]);
        }

        // If not found in newspapers, try photocopy services
        $photocopyService = PhotocopyService::where('name', 'LIKE', '%' . $request->barcode . '%')
            ->with('rawMaterials.product')
            ->first();

        if ($photocopyService) {
            $availableStock = $this->calculatePhotocopyServiceStock($photocopyService);
            
            // Format photocopy service to match product structure
            return response()->json([
                'product' => [
                    'id' => $photocopyService->id,
                    'name' => $photocopyService->name,
                    'barcode' => null, // Photocopy services don't have barcodes
                    'selling_price' => $photocopyService->totalprice,
                    'cost_price' => $photocopyService->price,
                    'stock_quantity' => $availableStock,
                    'discount' => 0,
                    'discounted_price' => $photocopyService->totalprice,
                    'is_photocopy' => true,
                    'service_details' => [
                        'size' => $photocopyService->size,
                        'side' => $photocopyService->side,
                        'pages' => $photocopyService->pages,
                        'color' => $photocopyService->color,
                        'service_charge' => $photocopyService->service_charge,
                    ],
                    'image' => null,
                ],
                'type' => 'photocopy_service',
                'error' => null,
            ]);
        }

        return response()->json([
            'product' => null,
            'error' => 'Product or Newspaper not found',
        ]);
    }

    public function getCoupon(Request $request)
    {
        $request->validate(
            ['code' => 'required|string'],
            ['code.required' => 'The coupon code missing.', 'code.string' => 'The coupon code must be a valid string.']
        );

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Invalid coupon code.']);
        }

        if (!$coupon->isValid()) {
            return response()->json(['error' => 'Coupon is expired or has been fully used.']);
        }

        return response()->json(['success' => 'Coupon applied successfully.', 'coupon' => $coupon]);
    }

    public function submit(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $customer = null;
        $products = $request->input('products', []);

        // Calculate totals
        $totalAmount = collect($products)->reduce(function ($carry, $item) {
            return $carry + ($item['quantity'] * $item['selling_price']);
        }, 0);

        $totalCost = collect($products)->reduce(function ($carry, $item) {
            $costPrice = $item['cost_price'] ?? 0;
            return $carry + ($item['quantity'] * $costPrice);
        }, 0);

        $productDiscounts = collect($products)->reduce(function ($carry, $item) {
            if (isset($item['discount']) && $item['discount'] > 0 && isset($item['apply_discount']) && $item['apply_discount'] == true) {
                $discountAmount = ($item['selling_price'] - $item['discounted_price']) * $item['quantity'];
                return $carry + $discountAmount;
            }
            return $carry;
        }, 0);

        $couponDiscount = isset($request->input('appliedCoupon')['discount']) ?
            floatval($request->input('appliedCoupon')['discount']) : 0;

        $totalDiscount = $productDiscounts + $couponDiscount;

        DB::beginTransaction();

        try {
            // Handle customer creation
            if ($request->input('customer.contactNumber') || $request->input('customer.name') || $request->input('customer.email')) {
                $phone = $request->input('customer.countryCode') . $request->input('customer.contactNumber');
                $customer = Customer::where('email', $request->input('customer.email'))->first();
                $customer1 = Customer::where('phone', $phone)->first();

                if ($customer) {
                    $email = '';
                } else {
                    $email = $request->input('customer.email');
                }

                if ($customer1) {
                    $phone = '';
                }

                if (!empty($phone) || !empty($email) || !empty($request->input('customer.name'))) {
                    $customer = Customer::create([
                        'name' => $request->input('customer.name'),
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $request->input('customer.address', ''),
                        'member_since' => now()->toDateString(),
                        'loyalty_points' => 0,
                    ]);
                }
            }

            // Create the sale record
            $sale = Sale::create([
                'customer_id' => $customer ? $customer->id : null,
                'employee_id' => $request->input('employee_id'),
                'user_id' => $request->input('userId'),
                'order_id' => $request->input('orderid'),
               'total_amount' => $request->input('paymentMethod') === 'card' 
        ? $totalAmount * 0.975 
        : $totalAmount,
                'discount' => $totalDiscount,
                'total_cost' => $totalCost,
                'payment_method' => $request->input('paymentMethod'),
                'sale_date' => now()->toDateString(),
                'cash' => $request->input('cash'),
                'custom_discount' => $request->input('custom_discount'),
            ]);

            // Process each item (product or newspaper)
            foreach ($products as $item) {
                $isNewspaper = isset($item['is_newspaper']) && $item['is_newspaper'] === true;
                $isPhotocopy = isset($item['is_photocopy']) && $item['is_photocopy'] === true;
                $isPrintout = isset($item['is_printout']) && $item['is_printout'] === true;
                $isLaminating = isset($item['is_laminating']) && $item['is_laminating'] === true;
                $isBinding = isset($item['is_binding']) && $item['is_binding'] === true;

                if ($isNewspaper) {
                    // Handle newspaper
                    $newspaperModel = Newspaper::find($item['id']);
                    
                    if (!$newspaperModel) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Newspaper not found: {$item['name']}"
                        ], 404);
                    }

                    $newStockQuantity = $newspaperModel->stock_quantity - $item['quantity'];

                    if ($newStockQuantity < 0) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Insufficient stock for newspaper: {$newspaperModel->name} ({$newspaperModel->stock_quantity} available)"
                        ], 423);
                    }

                    // Create sale item for newspaper
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'newspaper_id' => $item['id'],
                        'product_id' => null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['selling_price'],
                        'total_price' => $item['quantity'] * $item['selling_price'],
                    ]);

                    // Create stock transaction for newspaper
                    StockTransaction::create([
                        'newspaper_id' => $item['id'],
                        'transaction_type' => 'Sold',
                        'quantity' => $item['quantity'],
                        'transaction_date' => now(),
                    ]);

                    // Update newspaper stock
                    $newspaperModel->update([
                        'stock_quantity' => $newStockQuantity,
                    ]);
                } elseif ($isPhotocopy) {
                    // Handle photocopy service
                    $photocopyModel = PhotocopyService::with('rawMaterials.product')->find($item['id']);

                    if (!$photocopyModel) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Photocopy service not found: {$item['name']}"
                        ], 404);
                    }

                    // Check if service has raw materials defined
                    if ($photocopyModel->rawMaterials->count() > 0) {
                        // Check stock availability for each raw material
                        foreach ($photocopyModel->rawMaterials as $rawMaterial) {
                            $availableStock = RefillPhotocopy::where('product_id', $rawMaterial->product_id)->sum('stock');
                            
                            if ($availableStock < $item['quantity']) {
                                DB::rollBack();
                                return response()->json([
                                    'message' => "Insufficient stock for raw material '{$rawMaterial->product->name}' required for {$photocopyModel->name}. Available: {$availableStock}, Required: {$item['quantity']}"
                                ], 423);
                            }
                        }

                        // Deduct stock from raw materials
                        foreach ($photocopyModel->rawMaterials as $rawMaterial) {
                            $remainingQuantity = $item['quantity'];
                            $refills = RefillPhotocopy::where('product_id', $rawMaterial->product_id)
                                ->where('stock', '>', 0)
                                ->orderBy('id')
                                ->get();

                            foreach ($refills as $refill) {
                                if ($remainingQuantity <= 0) break;

                                if ($refill->stock >= $remainingQuantity) {
                                    $refill->stock -= $remainingQuantity;
                                    $refill->save();
                                    $remainingQuantity = 0;
                                } else {
                                    $remainingQuantity -= $refill->stock;
                                    $refill->stock = 0;
                                    $refill->save();
                                }
                            }

                            // Create stock transaction for raw material deduction
                            StockTransaction::create([
                                'product_id' => $rawMaterial->product_id,
                                'transaction_type' => 'Deducted',
                                'quantity' => $item['quantity'],
                                'transaction_date' => now(),
                                'notes' => "Used for photocopy service: {$photocopyModel->name}",
                            ]);
                        }
                    } else {
                        // If no raw materials defined, check refill stock directly (backward compatibility)
                        $refillStock = RefillPhotocopy::where('product_id', $item['id'])->sum('stock');

                        if ($refillStock < $item['quantity']) {
                            DB::rollBack();
                            return response()->json([
                                'message' => "Insufficient stock for photocopy service: {$photocopyModel->name} ({$refillStock} available)"
                            ], 423);
                        }

                        // Deduct stock from refill
                        $remainingQuantity = $item['quantity'];
                        $refills = RefillPhotocopy::where('product_id', $item['id'])->orderBy('id')->get();

                        foreach ($refills as $refill) {
                            if ($remainingQuantity <= 0) break;

                            if ($refill->stock >= $remainingQuantity) {
                                $refill->stock -= $remainingQuantity;
                                $refill->save();
                                $remainingQuantity = 0;
                            } else {
                                $remainingQuantity -= $refill->stock;
                                $refill->stock = 0;
                                $refill->save();
                            }
                        }
                    }

                    // Create sale item for photocopy service
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'photocopy_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['selling_price'],
                        'total_price' => $item['quantity'] * $item['selling_price'],
                    ]);

                    // Create stock transaction for photocopy service
                    StockTransaction::create([
                        'photocopy_service_id' => $item['id'],
                        'transaction_type' => 'Sold',
                        'quantity' => $item['quantity'],
                        'transaction_date' => now()->toDateString(),
                    ]);
                } elseif ($isPrintout) {
                    // Handle printout service
                    $printoutModel = PrintoutService::with('rawMaterials.product')->find($item['id']);

                    if (!$printoutModel) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Printout service not found: {$item['name']}"
                        ], 404);
                    }

                    // Check if service has raw materials defined
                    if ($printoutModel->rawMaterials->count() > 0) {
                        // Check stock availability for each raw material
                        foreach ($printoutModel->rawMaterials as $rawMaterial) {
                            $availableStock = RefillPrintout::where('product_id', $rawMaterial->product_id)->sum('total_stock');
                            
                            if ($availableStock < $item['quantity']) {
                                DB::rollBack();
                                return response()->json([
                                    'message' => "Insufficient stock for raw material '{$rawMaterial->product->name}' required for {$printoutModel->name}. Available: {$availableStock}, Required: {$item['quantity']}"
                                ], 423);
                            }
                        }

                        // Deduct stock from raw materials
                        foreach ($printoutModel->rawMaterials as $rawMaterial) {
                            $remainingQuantity = $item['quantity'];
                            $refills = RefillPrintout::where('product_id', $rawMaterial->product_id)
                                ->where('total_stock', '>', 0)
                                ->orderBy('id')
                                ->get();

                            foreach ($refills as $refill) {
                                if ($remainingQuantity <= 0) break;

                                if ($refill->total_stock >= $remainingQuantity) {
                                    $refill->total_stock -= $remainingQuantity;
                                    $refill->save();
                                    $remainingQuantity = 0;
                                } else {
                                    $remainingQuantity -= $refill->total_stock;
                                    $refill->total_stock = 0;
                                    $refill->save();
                                }
                            }

                            // Create stock transaction for raw material deduction
                            StockTransaction::create([
                                'product_id' => $rawMaterial->product_id,
                                'transaction_type' => 'Deducted',
                                'quantity' => $item['quantity'],
                                'transaction_date' => now(),
                                'notes' => "Used for printout service: {$printoutModel->name}",
                            ]);
                        }
                    }

                    // Create sale item for printout service
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'printout_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['selling_price'],
                        'total_price' => $item['quantity'] * $item['selling_price'],
                    ]);

                    // Create stock transaction for printout service
                    StockTransaction::create([
                        'printout_service_id' => $item['id'],
                        'transaction_type' => 'Sold',
                        'quantity' => $item['quantity'],
                        'transaction_date' => now()->toDateString(),
                    ]);
                } elseif ($isLaminating) {
                    // Handle laminating service
                    $laminatingModel = LaminatingService::with('rawMaterials.product')->find($item['id']);

                    if (!$laminatingModel) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Laminating service not found: {$item['name']}"
                        ], 404);
                    }

                    // Check if service has raw materials defined
                    if ($laminatingModel->rawMaterials->count() > 0) {
                        // Check stock availability for each raw material
                        foreach ($laminatingModel->rawMaterials as $rawMaterial) {
                            // Try to get stock by product_id first, then by product_code if needed
                            $availableStock = RefillLaminating::where('product_id', $rawMaterial->product_id)->sum('total_stock');
                            
                            // If no stock found by product_id, try by product_code
                            if ($availableStock == 0 && $rawMaterial->product) {
                                $availableStock = RefillLaminating::where('product_code', $rawMaterial->product->code)->sum('total_stock');
                            }
                            
                            if ($availableStock < $item['quantity']) {
                                DB::rollBack();
                                return response()->json([
                                    'message' => "Insufficient stock for raw material '{$rawMaterial->product->name}' required for {$laminatingModel->name}. Available: {$availableStock}, Required: {$item['quantity']}"
                                ], 423);
                            }
                        }

                        // Deduct stock from raw materials
                        foreach ($laminatingModel->rawMaterials as $rawMaterial) {
                            $remainingQuantity = $item['quantity'];
                            
                            // Try to get refills by product_id first, then by product_code if needed
                            $refills = RefillLaminating::where('product_id', $rawMaterial->product_id)
                                ->where('total_stock', '>', 0)
                                ->orderBy('id')
                                ->get();
                                
                            // If no refills found by product_id, try by product_code
                            if ($refills->isEmpty() && $rawMaterial->product) {
                                $refills = RefillLaminating::where('product_code', $rawMaterial->product->code)
                                    ->where('total_stock', '>', 0)
                                    ->orderBy('id')
                                    ->get();
                            }

                            foreach ($refills as $refill) {
                                if ($remainingQuantity <= 0) break;

                                if ($refill->total_stock >= $remainingQuantity) {
                                    $refill->total_stock -= $remainingQuantity;
                                    $refill->save();
                                    $remainingQuantity = 0;
                                } else {
                                    $remainingQuantity -= $refill->total_stock;
                                    $refill->total_stock = 0;
                                    $refill->save();
                                }
                            }

                            // Create stock transaction for raw material deduction
                            StockTransaction::create([
                                'product_id' => $rawMaterial->product_id,
                                'transaction_type' => 'Deducted',
                                'quantity' => $item['quantity'],
                                'transaction_date' => now(),
                                'notes' => "Used for laminating service: {$laminatingModel->name}",
                            ]);
                        }
                    }

                    // Create sale item for laminating service
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'laminating_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['selling_price'],
                        'total_price' => $item['quantity'] * $item['selling_price'],
                    ]);

                    // Create stock transaction for laminating service
                    StockTransaction::create([
                        'laminating_service_id' => $item['id'],
                        'transaction_type' => 'Sold',
                        'quantity' => $item['quantity'],
                        'transaction_date' => now()->toDateString(),
                    ]);
                } elseif ($isBinding) {
                    // Handle binding service
                    $bindingModel = BindingService::with('rawMaterials.product')->find($item['id']);

                    if (!$bindingModel) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Binding service not found: {$item['name']}"
                        ], 404);
                    }

                    // Check if service has raw materials defined
                    if ($bindingModel->rawMaterials->count() > 0) {
                        // Check stock availability for each raw material
                        foreach ($bindingModel->rawMaterials as $rawMaterial) {
                            // Try to get stock by product_id first, then by product_code if needed
                            $availableStock = BindingRefill::where('product_id', $rawMaterial->product_id)->sum('total_stock');
                            
                            // If no stock found by product_id, try by product_code
                            if ($availableStock == 0 && $rawMaterial->product) {
                                $availableStock = BindingRefill::where('product_code', $rawMaterial->product->code)->sum('total_stock');
                            }
                            
                            if ($availableStock < $item['quantity']) {
                                DB::rollBack();
                                return response()->json([
                                    'message' => "Insufficient stock for raw material '{$rawMaterial->product->name}' required for {$bindingModel->name}. Available: {$availableStock}, Required: {$item['quantity']}"
                                ], 423);
                            }
                        }

                        // Deduct stock from raw materials
                        foreach ($bindingModel->rawMaterials as $rawMaterial) {
                            $remainingQuantity = $item['quantity'];
                            
                            // Try to get refills by product_id first, then by product_code if needed
                            $refills = BindingRefill::where('product_id', $rawMaterial->product_id)
                                ->where('total_stock', '>', 0)
                                ->orderBy('id')
                                ->get();
                                
                            // If no refills found by product_id, try by product_code
                            if ($refills->isEmpty() && $rawMaterial->product) {
                                $refills = BindingRefill::where('product_code', $rawMaterial->product->code)
                                    ->where('total_stock', '>', 0)
                                    ->orderBy('id')
                                    ->get();
                            }

                            foreach ($refills as $refill) {
                                if ($remainingQuantity <= 0) break;

                                if ($refill->total_stock >= $remainingQuantity) {
                                    $refill->total_stock -= $remainingQuantity;
                                    $refill->save();
                                    $remainingQuantity = 0;
                                } else {
                                    $remainingQuantity -= $refill->total_stock;
                                    $refill->total_stock = 0;
                                    $refill->save();
                                }
                            }

                            // Create stock transaction for raw material deduction
                            StockTransaction::create([
                                'product_id' => $rawMaterial->product_id,
                                'transaction_type' => 'Deducted',
                                'quantity' => $item['quantity'],
                                'transaction_date' => now(),
                                'notes' => "Used for binding service: {$bindingModel->name}",
                            ]);
                        }
                    }

                    // Create sale item for binding service
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'binding_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['selling_price'],
                        'total_price' => $item['quantity'] * $item['selling_price'],
                    ]);

                    // Create stock transaction for binding service
                    StockTransaction::create([
                        'binding_service_id' => $item['id'],
                        'transaction_type' => 'Sold',
                        'quantity' => $item['quantity'],
                        'transaction_date' => now()->toDateString(),
                    ]);
                } else {
                    // Handle product
                    $productModel = Product::find($item['id']);
                    
                    if (!$productModel) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Product not found: {$item['name']}"
                        ], 404);
                    }

                    $newStockQuantity = $productModel->stock_quantity - $item['quantity'];

                    if ($newStockQuantity < 0) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Insufficient stock for product: {$productModel->name} ({$productModel->stock_quantity} available)",
                        ], 423);
                    }

                    if ($productModel->expire_date && now()->greaterThan(new \Carbon\Carbon($productModel->expire_date))) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "The product '{$productModel->name}' has expired (Expiration Date: {$productModel->expire_date}).",
                        ], 423);
                    }

                    // Create sale item for product
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['id'],
                        'newspaper_id' => null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['selling_price'],
                        'total_price' => $item['quantity'] * $item['selling_price'],
                    ]);

                    // Create stock transaction for product
                    StockTransaction::create([
                        'product_id' => $item['id'],
                        'transaction_type' => 'Sold',
                        'quantity' => $item['quantity'],
                        'transaction_date' => now(),
                        'supplier_id' => $productModel->supplier_id ?? null,
                    ]);

                    // Update product stock
                    $productModel->update([
                        'stock_quantity' => $newStockQuantity,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Sale recorded successfully!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while processing the sale.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   public function getNewspapers()
{
    try {
        $newspapers = Newspaper::select(
            'id', 
            'name', 
            'barcode', 
            'stock_quantity', 
            'selling_price', 
            'cost_price', 
            'discount', 
            'discount_price'
        )
        ->where('stock_quantity', '>', 0)
        ->get()
        ->map(function ($newspaper) {
            return [
                'id' => $newspaper->id,
                'name' => $newspaper->name,
                'barcode' => $newspaper->barcode,
                'stock_quantity' => $newspaper->stock_quantity,
                'selling_price' => $newspaper->selling_price,
                'cost_price' => $newspaper->cost_price ?? 0,
                'discount' => $newspaper->discount ?? 0,
                'discount_price' => $newspaper->discount_price ?? $newspaper->selling_price,
            ];
        });

        return response()->json(['newspapers' => $newspapers]);
    } catch (\Exception $e) {
        \Log::error('Error fetching newspapers: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch newspapers'], 500);
    }
}

    public function getPhotocopyServices()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        try {
            $photocopyServices = PhotocopyService::select(
                'id', 
                'name', 
                'size', 
                'side', 
                'pages', 
                'color', 
                'price', 
                'service_charge', 
                'totalprice'
            )
            ->with(['rawMaterials.product'])
            ->get()
            ->map(function ($service) {
                // Calculate available stock based on raw materials
                $availableStock = $this->calculatePhotocopyServiceStock($service);
                
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'size' => $service->size,
                    'side' => $service->side,
                    'pages' => $service->pages,
                    'color' => $service->color,
                    'selling_price' => $service->totalprice,
                    'cost_price' => $service->price,
                    'service_charge' => $service->service_charge,
                    'stock_quantity' => $availableStock,
                    'has_raw_materials' => $service->rawMaterials->count() > 0,
                    'raw_materials' => $service->rawMaterials->map(function ($rawMaterial) {
                        return [
                            'product_id' => $rawMaterial->product_id,
                            'product_name' => $rawMaterial->product->name ?? 'Unknown',
                            'available_stock' => RefillPhotocopy::where('product_id', $rawMaterial->product_id)->sum('stock')
                        ];
                    }),
                    'is_photocopy' => true,
                ];
            })
            ->filter(function ($service) {
                return $service['stock_quantity'] > 0; // Only return services with available stock
            })
            ->values();

            return response()->json(['photocopy_services' => $photocopyServices]);
        } catch (\Exception $e) {
            \Log::error('Error fetching photocopy services: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch photocopy services'], 500);
        }
    }

    private function calculatePhotocopyServiceStock($photocopyService)
    {
        // If no raw materials are defined, assume unlimited stock
        if ($photocopyService->rawMaterials->count() === 0) {
            return 9999; // Large number to indicate unlimited
        }

        $minStock = PHP_INT_MAX;

        foreach ($photocopyService->rawMaterials as $rawMaterial) {
            $availableStock = RefillPhotocopy::where('product_id', $rawMaterial->product_id)->sum('stock');
            $minStock = min($minStock, $availableStock);
        }

        return max(0, $minStock === PHP_INT_MAX ? 0 : $minStock);
    }

    public function getPrintoutServices()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        try {
            $printoutServices = PrintoutService::select(
                'id', 
                'name', 
                'size', 
                'side', 
                'pages', 
                'color', 
                'price', 
                'service_charge', 
                'totalprice'
            )
            ->with(['rawMaterials.product'])
            ->get()
            ->map(function ($service) {
                // Calculate available stock based on raw materials
                $availableStock = $this->calculatePrintoutServiceStock($service);
                
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'size' => $service->size,
                    'side' => $service->side,
                    'pages' => $service->pages,
                    'color' => $service->color,
                    'selling_price' => $service->totalprice,
                    'cost_price' => $service->price,
                    'service_charge' => $service->service_charge,
                    'stock_quantity' => $availableStock,
                    'has_raw_materials' => $service->rawMaterials->count() > 0,
                    'raw_materials' => $service->rawMaterials->map(function ($rawMaterial) {
                        return [
                            'product_id' => $rawMaterial->product_id,
                            'product_name' => $rawMaterial->product->name ?? 'Unknown',
                            'available_stock' => RefillPrintout::where('product_id', $rawMaterial->product_id)->sum('total_stock')
                        ];
                    }),
                    'is_printout' => true,
                ];
            })
            ->filter(function ($service) {
                return $service['stock_quantity'] > 0; // Only return services with available stock
            })
            ->values();

            return response()->json(['printout_services' => $printoutServices]);
        } catch (\Exception $e) {
            \Log::error('Error fetching printout services: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch printout services'], 500);
        }
    }

    private function calculatePrintoutServiceStock($printoutService)
    {
        // If no raw materials are defined, assume unlimited stock
        if ($printoutService->rawMaterials->count() === 0) {
            return 9999; // Large number to indicate unlimited
        }

        $minStock = PHP_INT_MAX;

        foreach ($printoutService->rawMaterials as $rawMaterial) {
            $availableStock = RefillPrintout::where('product_id', $rawMaterial->product_id)->sum('total_stock');
            $minStock = min($minStock, $availableStock);
        }

        return max(0, $minStock === PHP_INT_MAX ? 0 : $minStock);
    }

    public function getAllProducts()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        try {
            $products = [];

            // Get regular products
            $regularProducts = Product::select(
                'id', 
                'name', 
                'barcode', 
                'code', 
                'stock_quantity', 
                'selling_price', 
                'cost_price', 
                'discount', 
                'discounted_price'
            )
            ->where('stock_quantity', '>', 0)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'code' => $product->code,
                    'stock_quantity' => $product->stock_quantity,
                    'selling_price' => $product->selling_price,
                    'cost_price' => $product->cost_price ?? 0,
                    'discount' => $product->discount ?? 0,
                    'discounted_price' => $product->discounted_price ?? $product->selling_price,
                    'type' => 'product',
                ];
            });

            // Get newspapers
            $newspapers = Newspaper::select(
                'id', 
                'name', 
                'barcode', 
                'productcode',
                'stock_quantity', 
                'selling_price', 
                'cost_price', 
                'discount', 
                'discount_price'
            )
            ->where('stock_quantity', '>', 0)
            ->get()
            ->map(function ($newspaper) {
                return [
                    'id' => $newspaper->id,
                    'name' => $newspaper->name,
                    'barcode' => $newspaper->barcode,
                    'code' => $newspaper->productcode,
                    'stock_quantity' => $newspaper->stock_quantity,
                    'selling_price' => $newspaper->selling_price,
                    'cost_price' => $newspaper->cost_price ?? 0,
                    'discount' => $newspaper->discount ?? 0,
                    'discounted_price' => $newspaper->discount_price ?? $newspaper->selling_price,
                    'type' => 'newspaper',
                    'is_newspaper' => true,
                ];
            });

            // Get photocopy services
            $photocopyServices = PhotocopyService::with(['rawMaterials.product'])
                ->get()
                ->map(function ($service) {
                    $availableStock = $this->calculatePhotocopyServiceStock($service);
                    
                    return [
                        'id' => $service->id,
                        'name' => $service->name . ' (' . $service->size . ', ' . $service->side . ', ' . $service->color . ')',
                        'barcode' => null,
                        'code' => null,
                        'stock_quantity' => $availableStock,
                        'selling_price' => $service->totalprice,
                        'cost_price' => $service->price,
                        'discount' => 0,
                        'discounted_price' => $service->totalprice,
                        'type' => 'photocopy_service',
                        'is_photocopy' => true,
                        'service_details' => [
                            'size' => $service->size,
                            'side' => $service->side,
                            'pages' => $service->pages,
                            'color' => $service->color,
                            'service_charge' => $service->service_charge,
                        ],
                    ];
                })
                ->filter(function ($service) {
                    return $service['stock_quantity'] > 0;
                })
                ->values();

            // Combine all products
            $products = $regularProducts->concat($newspapers)->concat($photocopyServices);

            return response()->json(['products' => $products]);
        } catch (\Exception $e) {
            \Log::error('Error fetching all products: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch products'], 500);
        }
    }

    public function getLaminatingServices(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        try {
            $laminatingServices = LaminatingService::select(
                'id', 
                'name', 
                'pouch_size', 
                'price', 
                'service_amount'
            )
            ->with(['rawMaterials.product'])
            ->get()
            ->map(function ($service) {
                // Calculate available stock based on raw materials
                $availableStock = $this->calculateLaminatingServiceStock($service);
                
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'pouch_size' => $service->pouch_size,
                    'price' => $service->price, // Base price
                    'service_amount' => $service->service_amount, // Service charge
                    'selling_price' => $service->price + $service->service_amount, // Total price
                    'cost_price' => $service->price,
                    'stock_quantity' => $availableStock,
                    'discount' => 0,
                    'discounted_price' => $service->price + $service->service_amount,
                    'has_raw_materials' => $service->rawMaterials->count() > 0,
                    'raw_materials' => $service->rawMaterials->map(function ($rawMaterial) {
                        // Try to get stock by product_id first, then by product_code if needed
                        $availableStock = RefillLaminating::where('product_id', $rawMaterial->product_id)->sum('total_stock');
                        
                        // If no stock found by product_id, try by product_code
                        if ($availableStock == 0 && $rawMaterial->product) {
                            $availableStock = RefillLaminating::where('product_code', $rawMaterial->product->code)->sum('total_stock');
                        }
                        
                        return [
                            'product_id' => $rawMaterial->product_id,
                            'product_name' => $rawMaterial->product->name ?? 'Unknown',
                            'available_stock' => $availableStock
                        ];
                    }),
                    'is_laminating' => true,
                ];
            });

            // Filter by stock only if not explicitly requesting all services
            if (!$request->has('show_all') || !$request->boolean('show_all')) {
                $laminatingServices = $laminatingServices->filter(function ($service) {
                    return $service['stock_quantity'] > 0; // Only return services with available stock
                });
            }

            $laminatingServices = $laminatingServices->values();

            return response()->json(['laminating_services' => $laminatingServices]);
        } catch (\Exception $e) {
            \Log::error('Error fetching laminating services: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch laminating services'], 500);
        }
    }

    private function calculateLaminatingServiceStock($laminatingService)
    {
        // If no raw materials are defined, assume unlimited stock
        if ($laminatingService->rawMaterials->count() === 0) {
            return 9999; // Large number to indicate unlimited
        }

        $minStock = PHP_INT_MAX;

        foreach ($laminatingService->rawMaterials as $rawMaterial) {
            // Try to get stock by product_id first, then by product_code if needed
            $availableStock = RefillLaminating::where('product_id', $rawMaterial->product_id)->sum('total_stock');
            
            // If no stock found by product_id, try by product_code
            if ($availableStock == 0 && $rawMaterial->product) {
                $availableStock = RefillLaminating::where('product_code', $rawMaterial->product->code)->sum('total_stock');
            }
            
            $minStock = min($minStock, $availableStock);
        }

        return max(0, $minStock === PHP_INT_MAX ? 0 : $minStock);
    }

    public function getBindingServices(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        try {
            $bindingServices = BindingService::select(
                'id', 
                'name', 
                'binding_type',
                'pages', 
                'price', 
                'service_charge', 
                'totalprice'
            )
            ->with(['rawMaterials.product'])
            ->get()
            ->map(function ($service) {
                // Calculate available stock based on raw materials
                $availableStock = $this->calculateBindingServiceStock($service);
                
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'binding_type' => $service->binding_type,
                    'pages' => $service->pages,
                    'selling_price' => $service->totalprice,
                    'cost_price' => $service->price,
                    'service_charge' => $service->service_charge,
                    'stock_quantity' => $availableStock,
                    'has_raw_materials' => $service->rawMaterials->count() > 0,
                    'raw_materials' => $service->rawMaterials->map(function ($rawMaterial) {
                        // Try to get stock by product_id first, then by product_code if needed
                        $availableStock = BindingRefill::where('product_id', $rawMaterial->product_id)->sum('total_stock');
                        
                        // If no stock found by product_id, try by product_code
                        if ($availableStock == 0 && $rawMaterial->product) {
                            $availableStock = BindingRefill::where('product_code', $rawMaterial->product->code)->sum('total_stock');
                        }
                        
                        return [
                            'product_id' => $rawMaterial->product_id,
                            'product_name' => $rawMaterial->product->name ?? 'Unknown',
                            'available_stock' => $availableStock
                        ];
                    }),
                    'is_binding' => true,
                ];
            });

            // Filter by stock only if not explicitly requesting all services
            if (!$request->has('show_all') || !$request->boolean('show_all')) {
                $bindingServices = $bindingServices->filter(function ($service) {
                    return $service['stock_quantity'] > 0; // Only return services with available stock
                });
            }

            $bindingServices = $bindingServices->values();

            return response()->json(['binding_services' => $bindingServices]);
        } catch (\Exception $e) {
            \Log::error('Error fetching binding services: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch binding services'], 500);
        }
    }

    private function calculateBindingServiceStock($bindingService)
    {
        // If no raw materials are defined, assume unlimited stock
        if ($bindingService->rawMaterials->count() === 0) {
            return 9999; // Large number to indicate unlimited
        }

        $minStock = PHP_INT_MAX;

        foreach ($bindingService->rawMaterials as $rawMaterial) {
            // Try to get stock by product_id first, then by product_code if needed
            $availableStock = BindingRefill::where('product_id', $rawMaterial->product_id)->sum('total_stock');
            
            // If no stock found by product_id, try by product_code
            if ($availableStock == 0 && $rawMaterial->product) {
                $availableStock = BindingRefill::where('product_code', $rawMaterial->product->code)->sum('total_stock');
            }
            
            $minStock = min($minStock, $availableStock);
        }

        return max(0, $minStock === PHP_INT_MAX ? 0 : $minStock);
    }
}