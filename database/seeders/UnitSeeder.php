<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        Unit::insert([
            ['name' => 'Kg', 'image' => null],
            ['name' => 'Litre', 'image' => null],
            ['name' => 'Piece', 'image' => null],
        ]);
    }
}
