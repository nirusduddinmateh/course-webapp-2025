<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Borrow>
 */
class BorrowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $borrowed = fake()->dateTimeBetween('-14 days','now');
        $due      = (clone $borrowed)->modify('+7 days');
        $returned = fake()->boolean(60) ? (clone $borrowed)->modify('+'.rand(1,9).' days') : null;

        return [
            'user_id'      => \App\Models\User::factory(), // เดี๋ยวเรามีแอดมินจาก Step 2 แล้วก็ใช้ id นั้นได้
            'equipment_id' => \App\Models\Equipment::factory(),
            'borrowed_at'  => $borrowed,
            'due_at'       => $due,
            'returned_at'  => $returned,
            'status'       => $returned ? 'returned' : 'borrowed',
            'note'         => fake()->optional()->sentence(6),
        ];
    }
}
