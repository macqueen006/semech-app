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
            Permission::create(['name' => $permission]);
        }
    }
}
