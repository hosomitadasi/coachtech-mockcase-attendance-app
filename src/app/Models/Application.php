<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_record_id',
        'approval_status',
        'application_date',
        'new_date',
        'new_clock_in',
        'new_clock_out',
        'comment'
    ];
    // 一括登録できるカラムを提示。

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // この申請モデルと申請したユーザーとは親子関係であることを示す。

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
    // どの勤怠記録の修正かを紐付ける

    public function proposalBreaks()
    {
        return $this->hasMany(ApplicationBreak::class);
    }
    // 1つの申請が複数の休憩申請案を持つ。
    // 元の勤怠の休憩 -> 2回
    // 修正申請でも休憩を修正したい -> ApplicationBreak が2つ作られる
}
