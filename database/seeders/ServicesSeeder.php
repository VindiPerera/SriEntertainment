<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('services')->insertOrIgnore([
            ['id' => 1, 'name' => 'Photocopy', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Printout', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Laminating', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Binding', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}