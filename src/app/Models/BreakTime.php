<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'attendances_id',

    ];

    public function attendances()
    {
        return $this->belongsTo(Attendance::class);
    }
}
