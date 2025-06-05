<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'request_date',
        'original_clock_in',
        'original_clock_out',
        'original_breaks_json',
        'requested_clock_in',
        'requested_clock_out',
        'requested_breaks_json',
        'note',
        'status',
        'admin_id',
        'reviewed_at',
    ];


    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admins_id');
    }
}