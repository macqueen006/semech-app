<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CreateWriterUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'firstname' => 'Pisarz',
            'lastname' => 'Pisarz',
            'email' => 'writer@db.com',
            'image_path' => '/images/avatars/user.png',
            'password' => bcrypt('writer1234'),
            'bio' => 'Tech enthusiast and content creator passionate about sharing knowledge through writing. I cover topics ranging from web development to emerging technologies.',
            'website' => 'https://example.com',
            'twitter' => 'admin-user',
            'linkedin' => 'admin-user',
            'github' => 'admin-user',
        ]);

        $role = Role::create(['name' => 'Pisarz']);

        $permissions = [
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '19' => 19,
            '21' => 21,
        ];

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
