<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $superadmin = Role::create(['name' => 'superadmin']);
        $seller = Role::create(['name' => 'seller']);
        $buyer = Role::create(['name' => 'buyer']);

        $permissions = [
            'create product',
            'edit product',
            'delete product',
            'view orders',
            'place order'
        ];

        foreach ($permissions as $perm) {
            $permission = Permission::create(['name' => $perm]);
            $superadmin->givePermissionTo($permission);
        }

        $seller->givePermissionTo(['create product', 'edit product', 'delete product', 'view orders']);
        $buyer->givePermissionTo(['place order']);
    }
}
