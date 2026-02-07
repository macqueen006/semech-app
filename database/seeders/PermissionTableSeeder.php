<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Permissions
        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'post-list',
            'post-create',
            'post-edit',
            'post-delete',
            'post-highlight',
            'post-super-list',
            'category-list',
            'category-create',
            'category-edit',
            'category-delete',
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'comment-list',
            'comment-edit',
            'comment-delete',
            'comment-super-list',
            'image-list',
            'image-delete',
            'subscriber-list',
            'subscriber-view',
            'subscriber-delete',
            'subscriber-edit',
            'contact-list',
            'contact-view',
            'analytics-view',
            'analytics-export',
            'activity-log-view',
            'activity-log-delete',
            'advertisement-list',
            'advertisement-create',
            'advertisement-edit',
            'advertisement-delete',
            'page-list',
            'page-edit',
        ];
        foreach ($permissions as $permission) {
            // Use firstOrCreate instead of create
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}

/*$newPermissions = ['page-list', 'page-edit'];

foreach ($newPermissions as $permission) {
    if (!\Spatie\Permission\Models\Permission::where('name', $permission)->exists()) {
        \Spatie\Permission\Models\Permission::create(['name' => $permission, 'guard_name' => 'web']);
        echo "Created: {$permission}\n";
    } else {
        echo "Already exists: {$permission}\n";
    }
}

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "Done!\n";
exit

$role = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
$role->givePermissionTo(['page-list', 'page-edit']);
echo "Permissions assigned to admin role!\n";
exit*/
