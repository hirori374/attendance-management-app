<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rest;

class RestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = Rest::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('00:00', '22:00');
        $end = (clone $start)->modify('+' . rand(30, 180) . ' minutes');

        return [
            'attendance_id' => 1,
            'rest_start_time' => $start->format('H:i'),
            'rest_end_time' => $end->format('H:i'),
            'remarks' => null,
        ];
    }
}
