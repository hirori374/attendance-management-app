<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Rest extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_id',
        'rest_start_time',
        'rest_end_time',
        'remarks'
    ];
    protected $casts = [
        'rest_start_time' => 'string',
        'rest_end_time' => 'string',
    ];

    protected function restStartTimeFormatted(): Attribute
    {
        return Attribute::get(
            fn() => $this->rest_start_time
            ? Carbon::createFromFormat('H:i:s', $this->rest_start_time)->format('H:i')
            : null
        );
    }

    protected function restEndTimeFormatted(): Attribute
    {
        return Attribute::get(
            fn() => $this->rest_end_time
            ? Carbon::createFromFormat('H:i:s', $this->rest_end_time)->format('H:i')
            : null
        );
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function restCorrections()
    {
        return $this->hasMany(RestCorrection::class);
    }
}
