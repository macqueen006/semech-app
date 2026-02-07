<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionTableSeeder::class,
            CreateAdminUserSeeder::class,
            CreateWriterUserSeeder::class,
            CreateModUserSeeder::class,
            CategoriesSeeder::class,
            PageSeeder::class,
        ]);

        \App\Models\Post::factory(50)->create();

        $this->call([
            HighlightPostSeeder::class,
        ]);
    }
}
