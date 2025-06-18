<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceBreak;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_time',
        'total_break_time',
        'comment'
    ];

    protected $casts = [
        'date'              => 'datetime',
        'clock_in'          => 'datetime:H:i',
        'clock_out'         => 'datetime:H:i',
        'total_time'        => 'string',
        'total_break_time'  => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }
}
