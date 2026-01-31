<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CreateModUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'firstname' => 'Mod',
            'lastname' => 'Moderator',
            'email' => 'mod@db.com',
            'image_path' => '/images/avatars/user.png',
            'password' => bcrypt('mod1234'),
            'bio' => 'Tech enthusiast and content creator passionate about sharing knowledge through writing. I cover topics ranging from web development to emerging technologies.',
            'website' => 'https://example.com',
            'twitter' => 'admin-user',
            'linkedin' => 'admin-user',
            'github' => 'admin-user',
        ]);

        $role = Role::create(['name' => 'Moderator']);

        $permissions = [
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12,
            '13' => 13,
            '14' => 14,
            '15' => 15,
            '16' => 16,
            '17' => 17,
            '18' => 18,
            '19' => 19,
            '20' => 20,
            '21' => 21,
            '22' => 22,
        ];

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
