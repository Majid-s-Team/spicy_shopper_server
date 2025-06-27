<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreCategory;

class StoreCategorySeeder extends Seeder
{
    public function run(): void
    {
        StoreCategory::insert([
            ['name' => 'Grocery', 'image' => null],
            ['name' => 'Electronics', 'image' => null],
            ['name' => 'Clothing', 'image' => null],
        ]);
    }
}
