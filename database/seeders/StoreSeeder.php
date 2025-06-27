<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Store::insert([
            [
                'user_id' => 1,
                'store_category_id' => 1,
                'name' => 'Fresh Mart',
                'image' => null,
                'description' => 'Fresh groceries and more.'
            ],
            [
                'user_id' => 2,
                'store_category_id' => 2,
                'name' => 'ElectroHub',
                'image' => null,
                'description' => 'Best electronic gadgets.'
            ],
        ]);
    }
}
