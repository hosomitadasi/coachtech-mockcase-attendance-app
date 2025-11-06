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
    // ここに書かれた項目だけが、一括で登録できるようになる。

    protected $casts = [
        'date'              => 'datetime',
        'clock_in'          => 'datetime:H:i',
        'clock_out'         => 'datetime:H:i',
        'total_time'        => 'string',
        'total_break_time'  => 'string',
    ];
    // データベースに保存されている日付文字列を、自動で「日時オブジェクト」として扱う。

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // 勤怠情報と一人のユーザーに属しているという関係

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    // 勤怠修正などの申請を、この勤怠記録が「複数持っている」という関係。

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }
    // 休憩時間を複数持っていることを表す。
}
