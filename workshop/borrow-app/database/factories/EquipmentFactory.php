<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'equipment_category_id' => \App\Models\EquipmentCategory::factory(),
            'code'  => strtoupper(fake()->bothify('EQ-###')),
            'name'  => fake()->randomElement([
                'ThinkPad E14','Acer Swift','Canon 80D','Tripod','Mic Set'
            ]),
            'stock' => fake()->numberBetween(0, 10),
            'photo_path' => null,
        ];
    }
}
