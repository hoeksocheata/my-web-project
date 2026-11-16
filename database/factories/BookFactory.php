<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name,
            'category_id' => Category::query()->inRandomOrder()->value('id'),
            'price' => $this->faker->randomFloat(2, 5, 50),
            'stock' => $this->faker->numberBetween(0, 40),
            'description' => $this->faker->paragraph,
        ];
    }
}
