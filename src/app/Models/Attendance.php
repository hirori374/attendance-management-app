<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'date',
        'attendance_start_time',
        'attendance_end_time',
        'remarks'
    ];
    protected $casts = [
        'date' => 'date',
        'attendance_start_time' => 'string',
        'attendance_end_time' => 'string',
    ];

    protected function attendanceStartTimeFormatted(): Attribute
    {
        return Attribute::get(
            fn() => $this->attendance_start_time
            ? Carbon::createFromFormat('H:i:s', $this->attendance_start_time)->format('H:i')
            : null
        );
    }

    protected function attendanceEndTimeFormatted(): Attribute
    {
        return Attribute::get(
            fn() => $this->attendance_end_time
            ? Carbon::createFromFormat('H:i:s', $this->attendance_end_time)->format('H:i')
            : null
        );
    }

    public function user()
    {
    return $this->belongsTo(User::class);
    }
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }
    public function attendanceCorrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }
}
