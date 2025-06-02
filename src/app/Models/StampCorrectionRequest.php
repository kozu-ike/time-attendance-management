<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_date',
        'original_clock_in',
        'original_clock_out',
        'original_breaks_json',
        'requested_clock_in',
        'requested_clock_out',
        'requested_breaks_json',
        'note',
        'status',
        'admins_id',
        'reviewed_at',
        'attendances_id',
        'admins_id'
    ];


    public function attendances()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function admins()
    {
        return $this->belongsTo(Admin::class);
    }
}