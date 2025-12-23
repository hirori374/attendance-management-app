<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class RestCorrection extends Model
{
    use HasFactory;
    use HasFactory;
    protected $fillable = [
        'request_batch_id',
        'request_date',
        'user_id',
        'date',
        'rest_id',
        'rest_request_start_time',
        'rest_request_end_time',
        'remarks',
        'status'
    ];
    protected $casts = [
        'date' => 'date',
        'request_date' => 'date',
        'rest_request_start_time' => 'string',
        'rest_request_end_time' => 'string',
    ];

    protected function restRequestStartTimeFormatted(): Attribute
    {
        return Attribute::get(
            fn() => $this->rest_request_start_time
            ? Carbon::createFromFormat('H:i:s', $this->rest_request_start_time)->format('H:i')
            : null
        );
    }

    protected function restRequestEndTimeFormatted(): Attribute
    {
        return Attribute::get(
            fn() => $this->rest_request_end_time
            ? Carbon::createFromFormat('H:i:s', $this->rest_request_end_time)->format('H:i')
            : null
        );
    }
    public function rest()
    {
        return $this->belongsTo(Rest::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
