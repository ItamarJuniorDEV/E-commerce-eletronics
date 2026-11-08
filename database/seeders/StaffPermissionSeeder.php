<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class StaffPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::query()->firstOrCreate(
            ['slug' => 'users.manage'],
            ['description' => 'Gerenciar funcionários internos'],
        );
    }
}
