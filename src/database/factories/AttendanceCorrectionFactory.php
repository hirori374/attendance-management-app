<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Str;

class AttendanceCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = AttendanceCorrection::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('00:00', '22:00');
        $end = (clone $start)->modify('+' . rand(3, 9) . ' hours');

        return [
            'request_batch_id' => (string) \Illuminate\Support\Str::uuid(),
            'request_date' => $this->faker->date(),
            'user_id' => 1,
            'date' => null,
            'attendance_id' => null,
            'attendance_request_start_time' => $start->format('H:i'),
            'attendance_request_end_time' => $end->format('H:i'),
            'remarks' => $this->faker->realText(15),
            'status' => '承認待ち',
        ];
    }
}
