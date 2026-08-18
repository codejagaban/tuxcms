<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        // Create super admin
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@tuxcms.com',
            'is_super_admin' => true,
        ]);

        // Create a demo tenant
        $tenant = Tenant::create([
            'name' => 'Demo Site',
            'domain' => 'localhost',
            'owner_id' => $superAdmin->id,
            'plan' => 'pro',
        ]);

        // Attach super admin as tenant owner
        $tenant->users()->attach($superAdmin->id, ['role' => 'owner']);

        // Set tenant context for seeders that create tenant-scoped data
        app()->instance('current_tenant_id', $tenant->id);
        app()->instance('current_tenant', $tenant);

        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
