<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('quotes')->truncate();

        DB::table('quotes')->insert([
            ['client_name' => 'Ion Popescu',   'car_plate' => 'B123XYZ', 'insurer_name' => 'AsiguratorX', 'price' => 545.50, 'created_at' => now(), 'updated_at' => now()],
            ['client_name' => 'Maria Ionescu', 'car_plate' => 'CJ45ABC', 'insurer_name' => 'AsiguratorY', 'price' => 480.00, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
