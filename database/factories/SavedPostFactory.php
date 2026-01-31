<?php

namespace Database\Factories;

use App\Models\SavedPost;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavedPostFactory extends Factory
{
    protected $model = SavedPost::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'excerpt' => fake()->paragraph(),
            'body' => '<p>' . fake()->paragraphs(3, true) . '</p>',
            'image_path' => '/images/posts/' . fake()->slug() . '.jpg',
            'category_id' => Category::factory(),  // Changed this line
            'is_published' => false,
            'read_time' => fake()->numberBetween(1, 10),
        ];
    }
}
