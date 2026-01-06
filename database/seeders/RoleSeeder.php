<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_name' => 'admin', 'description' => 'Administrator with full access'],
            ['role_name' => 'cashier', 'description' => 'Kasir responsible for transactions'],
            ['role_name' => 'chef', 'description' => 'Koki responsible for preparing food'],
            ['role_name' => 'customer','description' => 'Pelanggan'],
        ];

        DB::table('roles')->insert($roles);
    }
}
