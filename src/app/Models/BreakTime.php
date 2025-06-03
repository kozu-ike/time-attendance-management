<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    protected $fillable = ['attendance_id', 'break_in', 'break_out'];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
