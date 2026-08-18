<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'view posts',
            'create categories',
            'edit categories',
            'delete categories',
            'create comments',
            'approve comments',
            'delete comments',
            'manage settings',
            'manage media',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($permissions);

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->givePermissionTo([
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'view posts',
            'create categories',
            'edit categories',
            'create comments',
            'approve comments',
            'delete comments',
            'manage media',
        ]);

        $author = Role::firstOrCreate(['name' => 'author']);
        $author->givePermissionTo([
            'create posts',
            'edit posts',
            'view posts',
            'create comments',
            'manage media',
        ]);

        $subscriber = Role::firstOrCreate(['name' => 'subscriber']);
        $subscriber->givePermissionTo([
            'view posts',
            'create comments',
        ]);
    }
}
