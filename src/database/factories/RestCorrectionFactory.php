<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\Rest;

class RestCorrectionFactory extends Factory
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
            'request_batch_id' => (string) \Illuminate\Support\Str::uuid(),
            'request_date' => $this->faker->date(),
            'user_id' => 1,
            'date' => null,
            'rest_id' => null,
            'rest_request_start_time' => $start->format('H:i'),
            'rest_request_end_time' => $end->format('H:i'),
            'remarks' => realText(15),
            'status' => '承認待ち',
        ];
    }
}
