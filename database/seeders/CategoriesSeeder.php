<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'backgroundColor' => '#3498db', 'textColor' => '#ffffff'],
            ['name' => 'Travel', 'slug' => 'travel', 'backgroundColor' => '#2ecc71', 'textColor' => '#ffffff'],
            ['name' => 'Food & Cooking', 'slug' => 'food-cooking', 'backgroundColor' => '#e74c3c', 'textColor' => '#ffffff'],
            ['name' => 'Fashion', 'slug' => 'fashion', 'backgroundColor' => '#9b59b6', 'textColor' => '#ffffff'],
            ['name' => 'Health & Fitness', 'slug' => 'health-fitness', 'backgroundColor' => '#27ae60', 'textColor' => '#ffffff'],
            ['name' => 'Science', 'slug' => 'science', 'backgroundColor' => '#3498db', 'textColor' => '#ffffff'],
            ['name' => 'Entertainment', 'slug' => 'entertainment', 'backgroundColor' => '#e67e22', 'textColor' => '#ffffff'],
            ['name' => 'Lifestyle', 'slug' => 'lifestyle', 'backgroundColor' => '#f39c12', 'textColor' => '#ffffff'],
            ['name' => 'Business & Finance', 'slug' => 'business-finance', 'backgroundColor' => '#34495e', 'textColor' => '#ffffff'],
            ['name' => 'Education', 'slug' => 'education', 'backgroundColor' => '#16a085', 'textColor' => '#ffffff'],
            ['name' => 'Sports', 'slug' => 'sports', 'backgroundColor' => '#e74c3c', 'textColor' => '#ffffff'],
            ['name' => 'Music', 'slug' => 'music', 'backgroundColor' => '#2980b9', 'textColor' => '#ffffff'],
            ['name' => 'Art & Design', 'slug' => 'art-design', 'backgroundColor' => '#8e44ad', 'textColor' => '#ffffff'],
            ['name' => 'DIY', 'slug' => 'diy', 'backgroundColor' => '#d35400', 'textColor' => '#ffffff'],
            ['name' => 'Gaming', 'slug' => 'gaming', 'backgroundColor' => '#c0392b', 'textColor' => '#ffffff'],
        ];


        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'backgroundColor' => $category['backgroundColor'],
                'textColor' => $category['textColor'],
            ]);
        }
    }
}
