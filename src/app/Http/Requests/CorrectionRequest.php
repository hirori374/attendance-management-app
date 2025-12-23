<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attendance_start_time' => [
                'required',
                'date_format:H:i',
                'before:attendance_end_time'
            ],
            'attendance_end_time' => [
                'required',
                'date_format:H:i',
                'after:attendance_start_time'
            ],
            'rest_start_time.*' => [
                'nullable',
                'date_format:H:i',
                'after:attendance_start_time',
                'before:attendance_end_time'
            ],
            'rest_end_time.*' => [
                'nullable',
                'date_format:H:i',
                'before:attendance_end_time'
            ],
            'remarks' => [
                'required',
                'string',
                'max:255'
            ],
        ];
    }
    public function messages()
    {
        return [
            'attendance_start_time.required' => '出勤時間を入力してください',
            'attendance_end_time.required' => '退勤時間を入力してください',
            'attendance_start_time.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'attendance_end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'rest_start_time.*.after' => '休憩時間が不適切な値です',
            'rest_start_time.*.before' => '休憩時間が不適切な値です',
            'rest_end_time.*.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください'
        ];
    }
}
