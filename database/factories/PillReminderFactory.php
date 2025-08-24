<?php

namespace Database\Factories;

use App\Models\PillReminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PillReminderFactory extends Factory
{
    protected $model = PillReminder::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['بنادول', 'فيتامين C', 'مسكن', 'مضاد حيوي']),
            'dosage' => $this->faker->randomElement(['100mg', '250mg', '500mg']),
            'notes' => $this->faker->sentence(),
            'time' => $this->faker->time('H:i'),
            'repeat' => $this->faker->boolean(),
            'days' => $this->faker->randomElements(['saturday','sunday','monday','tuesday','wednesday','thursday','friday'], rand(1, 3)),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}