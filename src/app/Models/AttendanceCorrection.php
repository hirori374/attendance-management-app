<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class AttendanceCorrection extends Model
{
    use HasFactory;
    protected $fillable = [
        'request_batch_id',
        'request_date',
        'user_id',
        'date',
        'attendance_id',
        'attendance_request_start_time',
        'attendance_request_end_time',
        'remarks',
        'status'
    ];
    protected $casts = [
        'date' => 'date',
        'request_date' => 'date',
        'attendance_request_start_time' => 'string',
        'attendance_request_end_time' => 'string',
    ];

    protected function attendanceRequestStartTimeFormatted(): Attribute
    {
        return Attribute::get(
            fn() => $this->attendance_request_start_time
            ? Carbon::createFromFormat('H:i:s', $this->attendance_request_start_time)->format('H:i')
            : null
        );
    }

    protected function attendanceRequestEndTimeFormatted(): Attribute
    {
        return Attribute::get(
            fn() => $this->attendance_request_end_time
            ? Carbon::createFromFormat('H:i:s', $this->attendance_request_end_time)->format('H:i')
            : null
        );
    }
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
