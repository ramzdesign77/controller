<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalog = [
            ['Beras Premium', 'Sembako'],
            ['Minyak Goreng', 'Sembako'],
            ['Gula Pasir', 'Sembako'],
            ['Tepung Terigu', 'Sembako'],
            ['Garam Dapur', 'Bumbu & Bahan Masak'],
            ['Mi Instan', 'Makanan Instan'],
            ['Susu UHT', 'Susu & Olahan'],
            ['Kopi Bubuk', 'Minuman'],
            ['Teh Celup', 'Minuman'],
            ['Air Mineral', 'Minuman'],
            ['Roti Tawar', 'Roti & Kue'],
            ['Biskuit', 'Camilan'],
            ['Cokelat', 'Camilan'],
            ['Sabun Mandi', 'Perawatan Diri'],
            ['Sampo', 'Perawatan Diri'],
            ['Pasta Gigi', 'Perawatan Diri'],
            ['Deterjen', 'Kebutuhan Rumah'],
            ['Pembersih Lantai', 'Kebutuhan Rumah'],
            ['Tisu', 'Kebutuhan Rumah'],
            ['Popok Bayi', 'Perlengkapan Bayi'],
            ['Makanan Kucing', 'Kebutuhan Hewan'],
            ['Telur Ayam', 'Bahan Segar'],
            ['Sarden Kaleng', 'Makanan Kaleng'],
            ['Kecap Manis', 'Bumbu & Bahan Masak'],
            ['Saus Sambal', 'Bumbu & Bahan Masak'],
        ];

        for ($number = 1; $number <= 200; $number++) {
            [$productName, $category] = $catalog[($number - 1) % count($catalog)];

            Product::firstOrCreate(
                ['name' => sprintf('Demo %03d - %s', $number, $productName)],
                [
                    'category' => $category,
                    'price' => fake()->randomFloat(2, 500, 500000),
                    'stock' => fake()->numberBetween(0, 200),
                    'description' => fake()->sentence(),
                    'is_active' => fake()->boolean(90),
                ],
            );
        }

        $samples = [
            ['POS-DEMO-001', 'Indomie Goreng', 'Makanan Instan', 3500, 50],
            ['POS-DEMO-002', 'Aqua 600ml', 'Minuman', 4000, 50],
            ['POS-DEMO-003', 'Teh Botol Sosro 350ml', 'Minuman', 5000, 40],
            ['POS-DEMO-004', 'Beras Premium 5kg', 'Sembako', 72000, 25],
        ];

        foreach ($samples as [$barcode, $name, $category, $price, $stock]) {
            Product::firstOrCreate(
                ['barcode' => $barcode],
                [
                    'name' => $name,
                    'category' => $category,
                    'price' => $price,
                    'stock' => $stock,
                    'description' => 'Produk contoh untuk demonstrasi kasir.',
                    'is_active' => true,
                ],
            );
        }
    }
}
