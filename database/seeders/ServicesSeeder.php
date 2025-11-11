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
        DB::table('services')->insert([
            ['id' => 1, 'name' => 'Photocopy'],
            ['id' => 2, 'name' => 'Printout'],
            ['id' => 3, 'name' => 'Laminating'],
            ['id' => 4, 'name' => 'Binding'],
        ]);
    }
}