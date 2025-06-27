<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\StoreCategorySeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\UnitSeeder;
use Database\Seeders\StoreSeeder;
use Database\Seeders\ProductSeeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
            $this->call([
            StoreCategorySeeder::class,
            ProductCategorySeeder::class,
            UnitSeeder::class,
            StoreSeeder::class,
            ProductSeeder::class,
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
