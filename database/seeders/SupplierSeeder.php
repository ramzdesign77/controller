<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT. Indofood',
                'phone' => '021-12345678',
                'address' => 'Jl. Sudirman No. 1, Jakarta'
            ],
            [
                'name' => 'PT. Unilever',
                'phone' => '021-87654321',
                'address' => 'Jl. Gatot Subroto No. 10, Jakarta'
            ],
            [
                'name' => 'PT. Mayora',
                'phone' => '021-11223344',
                'address' => 'Jl. Daan Mogot KM 18, Tangerang'
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}