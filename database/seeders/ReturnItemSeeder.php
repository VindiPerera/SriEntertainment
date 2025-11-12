<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReturnItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;

class ReturnItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing products, customers and sales for the relationships
        $products = Product::limit(5)->get();
        $customers = Customer::limit(5)->get();
        $sales = Sale::limit(5)->get();

        if ($products->count() > 0 && $customers->count() > 0) {
            $reasons = ['Damaged', 'Wrong item', 'Customer dissatisfied', 'Expired', 'Defective', 'Customer changed mind'];
            
            for ($i = 0; $i < 10; $i++) {
                ReturnItem::create([
                    'sale_id' => $sales->count() > 0 ? $sales->random()->id : null,
                    'customer_id' => $customers->random()->id,
                    'product_id' => $products->random()->id,
                    'quantity' => rand(1, 5),
                    'reason' => $reasons[array_rand($reasons)],
                    'return_date' => now()->subDays(rand(0, 30))->format('Y-m-d'),
                ]);
            }
        }
    }
}