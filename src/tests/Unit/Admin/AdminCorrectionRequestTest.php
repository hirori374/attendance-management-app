<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\CorrectionRequest;
use Illuminate\Support\Facades\Validator;

class AdminCorrectionRequestTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    // 修正処理
    public function testValidDataPasses()
    {
        $request = new CorrectionRequest();

        $validator = $this->app['validator']->make(
            [
                'attendance_start_time' => '10:00',
                'attendance_end_time' => '18:00',
                'rest_start_time' => ['13:00'],
                'rest_end_time' => ['14:00'],
                'remarks' => 'test',
            ],
            $request->rules(),
            $request->messages()
        );

        $this->assertFalse($validator->fails());
    }
    // 出勤時間が退勤時間よりも後の場合
    public function testAttendanceStartTimeBefore()
    {
        $request = new CorrectionRequest();

        $validator = $this->app['validator']->make(
            [
                'attendance_start_time' => '19:00',
                'attendance_end_time' => '18:00',
                'remarks' => 'test',
            ],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());

        $this->assertSame(
            ['出勤時間もしくは退勤時間が不適切な値です'],
            $validator->errors()->get('attendance_start_time')
        );
    }
    // 休憩開始時間が退勤時間よりも後の場合
    public function testRestStartTimeBefore()
    {
        $request = new CorrectionRequest();

        $validator = $this->app['validator']->make(
            [
                'attendance_start_time' => '10:00',
                'attendance_end_time' => '18:00',
                'rest_start_time' => ['19:00'],
                'rest_end_time' => ['14:00'],
                'remarks' => 'test',
            ],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());

        $this->assertSame(
            ['休憩時間が不適切な値です'],
            $validator->errors()->get('rest_start_time.0')
        );
    }
    // 休憩終了時間が退勤時間よりも後の場合
    public function testRestEndTimeBefore()
    {
        $request = new CorrectionRequest();

        $validator = $this->app['validator']->make(
            [
                'attendance_start_time' => '10:00',
                'attendance_end_time' => '18:00',
                'rest_start_time' => ['13:00'],
                'rest_end_time' => ['19:00'],
                'remarks' => 'test',
            ],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());

        $this->assertSame(
            ['休憩時間もしくは退勤時間が不適切な値です'],
            $validator->errors()->get('rest_end_time.0')
        );
    }
    // 備考が空白の場合
    public function testRemarksRequired()
    {
        $request = new CorrectionRequest();

        $validator = $this->app['validator']->make(
            [
                'attendance_start_time' => '10:00',
                'attendance_end_time' => '18:00',
                'rest_start_time' => ['13:00'],
                'rest_end_time' => ['14:00'],
                'remarks' => '',
            ],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());

        $this->assertSame(
            ['備考を記入してください'],
            $validator->errors()->get('remarks')
        );
    }
}
