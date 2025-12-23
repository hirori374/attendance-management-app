<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'attendance_id' => '1',
            'rest_start_time' => '13:00:00',
            'rest_end_time' => '13:30:00',
        ];
        DB::table('rests')->insert($param);
        $param = [
            'attendance_id' => '3',
            'rest_start_time' => '13:00:00',
            'rest_end_time' => '14:00:00',
        ];
        DB::table('rests')->insert($param);
        $param = [
            'attendance_id' => '4',
            'rest_start_time' => '12:00:00',
            'rest_end_time' => '12:30:00',
        ];
        DB::table('rests')->insert($param);
        $param = [
            'attendance_id' => '4',
            'rest_start_time' => '16:00:00',
            'rest_end_time' => '17:00:00',
        ];
        DB::table('rests')->insert($param);
        $param = [
            'attendance_id' => '5',
            'rest_start_time' => '13:30:00',
            'rest_end_time' => '14:30:00',
        ];
        DB::table('rests')->insert($param);
        $param = [
            'attendance_id' => '7',
            'rest_start_time' => '14:00:00',
            'rest_end_time' => '15:00:00',
        ];
        DB::table('rests')->insert($param);
        $param = [
            'attendance_id' => '8',
            'rest_start_time' => '13:00:00',
            'rest_end_time' => '13:30:00',
        ];
        DB::table('rests')->insert($param);
        $param = [
            'attendance_id' => '8',
            'rest_start_time' => '19:00:00',
            'rest_end_time' => '19:30:00',
        ];
        DB::table('rests')->insert($param);
    }
}
