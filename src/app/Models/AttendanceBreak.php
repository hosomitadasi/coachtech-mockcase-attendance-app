<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    protected $table = 'breaks';
    // このモデルが対応するデータベースのテーブル名を手動で指定。テーブル名がbreaks.table.phpなのでこのように設定。

    protected $fillable = [
        'attendance_record_id',
        'break_in',
        'break_out',
    ];
    // 一括で登録してもいいカラムを一覧で設定。コントローラでAttendanceBreak::create([...]);のようにまとめて保存する際に、ここの項目が安全に登録される。

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
    // AttendanceBreakが１つのAttendanceRecordに属している関係。
}
