<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => $this->faker->word, // اسم التصنيف
            'position'  => $this->faker->numberBetween(1, 10),
            'active'    => true,
            'show_home' => $this->faker->boolean(90), // 90% احتمال يظهر في الصفحة الرئيسية
        ];
    }
}
