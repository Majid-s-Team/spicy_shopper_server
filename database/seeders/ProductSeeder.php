<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::insert([
            [
                'store_id' => 1,
                'product_category_id' => 1,
                'unit_id' => 1, // Kg
                'name' => 'Apple Juice',
                'price' => 3.99,
                'quantity' => 50,
                'discount' => 10,
                'description' => 'Fresh apple juice with no added sugar.',
                'image' => null
            ],
            [
                'store_id' => 2,
                'product_category_id' => 2,
                'unit_id' => 3, // Piece
                'name' => 'LED Bulb',
                'price' => 1.25,
                'quantity' => 200,
                'discount' => 0,
                'description' => 'Energy saving LED bulb.',
                'image' => null
            ],
        ]);
    }
}
