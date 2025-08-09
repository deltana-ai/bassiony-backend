<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => $this->faker->company, // اسم الشركة أو البراند
            'position'  => $this->faker->numberBetween(1, 10),
            'active'    => true,
            'show_home' => $this->faker->boolean(80), // 80% احتمال يظهر في الهوم
        ];
    }
}
