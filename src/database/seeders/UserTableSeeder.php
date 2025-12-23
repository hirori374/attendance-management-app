<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => '管理者',
            'email' => 'manager@example.com',
            'password' => Hash::make('manager0000'),
            'authority' => 'admin',
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => '一般ユーザー1',
            'email' => 'general1@example.com',
            'password' => Hash::make('general1111'),
            'authority' => 'general',
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => '一般ユーザー2',
            'email' => 'general2@example.com',
            'password' => Hash::make('general2222'),
            'authority' => 'general',
        ];
        DB::table('users')->insert($param);
    }
}
