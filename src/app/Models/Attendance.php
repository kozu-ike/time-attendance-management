<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'total_break_minutes',
        'total_work_minutes',
        'status',
    ];

    // app/Models/Attendance.php
    protected static function booted()
    {
        static::saved(function ($attendance) {
            if ($attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);
                if ($clockOut->lt($clockIn)) {
                    $clockOut->addDay();
                }

                $attendance->load('breaks');

                $totalBreakMinutes = $attendance->breaks->sum(function ($break) {
                    $in = Carbon::parse($break->break_in);
                    $out = Carbon::parse($break->break_out);
                    if ($out->lt($in)) $out->addDay();
                    return $out->diffInMinutes($in);
                });

                $totalWorkMinutes = $clockOut->diffInMinutes($clockIn) - $totalBreakMinutes;

                if (
                    $attendance->total_break_minutes !== $totalBreakMinutes
                    || $attendance->total_work_minutes !== $totalWorkMinutes
                ) {
                    $attendance->total_break_minutes = $totalBreakMinutes;
                    $attendance->total_work_minutes = $totalWorkMinutes;
                    $attendance->saveQuietly();
                }
            }
        });
    }




    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }
}
