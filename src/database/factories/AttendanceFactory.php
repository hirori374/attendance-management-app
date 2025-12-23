<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => 1,
            'date' => $this->faker->date(),
            'attendance_start_time' => null,
            'attendance_end_time' => null,
            'remarks' => null,
        ];
    }
}
