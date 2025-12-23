<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => '2',
            'date' => '2025-12-01',
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '17:00:00',
        ];
        DB::table('attendances')->insert($param);
        $param = [
            'user_id' => '2',
            'date' => '2025-12-09',
            'attendance_start_time' => '12:00:00',
            'attendance_end_time' => '16:00:00',
        ];
        DB::table('attendances')->insert($param);
        $param = [
            'user_id' => '2',
            'date' => '2025-12-10',
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '17:00:00',
        ];
        DB::table('attendances')->insert($param);
        $param = [
            'user_id' => '2',
            'date' => '2025-12-15',
            'attendance_start_time' => '09:00:00',
            'attendance_end_time' => '21:00:00',
        ];
        DB::table('attendances')->insert($param);
        $param = [
            'user_id' => '3',
            'date' => '2025-12-01',
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '17:00:00',
        ];
        DB::table('attendances')->insert($param);
        $param = [
            'user_id' => '3',
            'date' => '2025-12-08',
            'attendance_start_time' => '12:00:00',
            'attendance_end_time' => '16:00:00',
        ];
        DB::table('attendances')->insert($param);
        $param = [
            'user_id' => '3',
            'date' => '2025-12-10',
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '17:00:00',
        ];
        DB::table('attendances')->insert($param);
        $param = [
            'user_id' => '3',
            'date' => '2025-12-20',
            'attendance_start_time' => '09:00:00',
            'attendance_end_time' => '21:00:00',
        ];
        DB::table('attendances')->insert($param);
    }
}
