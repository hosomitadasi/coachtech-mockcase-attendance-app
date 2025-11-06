<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Application;

class ApplicationBreak extends Model
{
    protected $table = 'application_breaks';
    // 対応するテーブル名を明示する。
    protected $fillable = ['application_id','break_in','break_out'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
    // この休憩案は 1つの修正申請に属していることを示す。
}
