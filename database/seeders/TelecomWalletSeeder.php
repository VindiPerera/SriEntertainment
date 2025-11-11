<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Operator;
use App\Models\OperatorRate;
use App\Models\ReloadPackage;

class TelecomWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Operators
        $operators = [
            [
                'name' => 'Mobitel',
                'code' => 'MOB',
                'business_model' => 'sale_commission',
                'default_percentage' => 6.00,
                'is_active' => true,
                'logo_url' => '/images/operators/mobitel.png',
                'description' => 'Mobitel operator - Commission applies on sale',
            ],
            [
                'name' => 'Dialog',
                'code' => 'DIA',
                'business_model' => 'sale_commission',
                'default_percentage' => 6.00,
                'is_active' => true,
                'logo_url' => '/images/operators/dialog.png',
                'description' => 'Dialog operator - Commission applies on sale',
            ],
            [
                'name' => 'Airtel',
                'code' => 'AIR',
                'business_model' => 'deposit_bonus',
                'default_percentage' => 4.00,
                'is_active' => true,
                'logo_url' => '/images/operators/airtel.png',
                'description' => 'Airtel operator - Bonus on deposit',
            ],
            [
                'name' => 'Hutch',
                'code' => 'HUT',
                'business_model' => 'deposit_bonus',
                'default_percentage' => 4.00,
                'is_active' => true,
                'logo_url' => '/images/operators/hutch.png',
                'description' => 'Hutch operator - Bonus on deposit',
            ],
        ];

        foreach ($operators as $operatorData) {
            $operator = Operator::create($operatorData);

            // Create default operator rates
            OperatorRate::create([
                'operator_id' => $operator->id,
                'user_id' => null, // Global rate
                'employee_id' => null,
                'rate_type' => $operator->business_model,
                'percentage' => $operator->default_percentage,
                'effective_from' => now(),
                'effective_to' => null,
                'is_active' => true,
            ]);

            // Create reload packages
            $packages = $this->getPackagesForOperator($operator->code);
            foreach ($packages as $packageData) {
                ReloadPackage::create([
                    'operator_id' => $operator->id,
                    'name' => $packageData['name'],
                    'code' => $operator->code . '-' . $packageData['value'],
                    'face_value' => $packageData['value'],
                    'description' => "Rs. {$packageData['value']} reload package for {$operator->name}",
                    'is_active' => true,
                    'sort_order' => $packageData['sort_order'],
                ]);
            }
        }

        $this->command->info('Telecom wallet system seeded successfully!');
    }

    /**
     * Get reload packages for an operator
     */
    private function getPackagesForOperator($code)
    {
        $commonPackages = [
            ['name' => 'Rs. 50 Package', 'value' => 50, 'sort_order' => 1],
            ['name' => 'Rs. 100 Package', 'value' => 100, 'sort_order' => 2],
            ['name' => 'Rs. 200 Package', 'value' => 200, 'sort_order' => 3],
            ['name' => 'Rs. 500 Package', 'value' => 500, 'sort_order' => 4],
            ['name' => 'Rs. 1000 Package', 'value' => 1000, 'sort_order' => 5],
            ['name' => 'Rs. 1500 Package', 'value' => 1500, 'sort_order' => 6],
            ['name' => 'Rs. 2000 Package', 'value' => 2000, 'sort_order' => 7],
            ['name' => 'Rs. 5000 Package', 'value' => 5000, 'sort_order' => 8],
        ];

        return $commonPackages;
    }
}
