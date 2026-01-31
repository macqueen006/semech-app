<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'firstname' => 'Admin',
            'lastname' => 'Administrator',
            'email' => 'admin@db.com',
            'image_path' => '/images/avatars/user.png',
            'password' => bcrypt('admin1234'),
            'bio' => 'Tech enthusiast and content creator passionate about sharing knowledge through writing. I cover topics ranging from web development to emerging technologies.',
            'is_admin' => true,
            'website' => 'https://example.com',
            'twitter' => 'admin-user',
            'linkedin' => 'admin-user',
            'github' => 'admin-user',
        ]);

        $role = Role::create(['name' => 'Admin']);

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
