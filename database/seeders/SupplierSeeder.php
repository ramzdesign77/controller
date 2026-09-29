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
                'email' => 'indofood@supplier.test',
                'phone' => '021-12345678',
            ],
            [
                'name' => 'PT. Unilever',
                'email' => 'unilever@supplier.test',
                'phone' => '021-87654321',
            ],
            [
                'name' => 'PT. Mayora',
                'email' => 'mayora@supplier.test',
                'phone' => '021-11223344',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['email' => $supplier['email']], $supplier);
        }
    }
}
