<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // HasApiTokensでSanctumを使用したAPIトークン認証を使えるようにする。HasFactoryでファクトリを使用したダミーデータ生成に対応する。NotifiableはメールやSlackなどを使用した通知送信が可能になる機能。

    protected $fillable = [
        'name',
        'email',
        'password',
        'admin_status',
        'attendance_status'
    ];
    // フォーム送信や create() でセットできるカラムのリスト。ここに入っているものが一括で入れることができるようになる。

    protected $hidden = [
        'password',
        'remember_token',
    ];
    // 外部に見せないようにするコード。パスワードなんかをここに入れる。

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    // データベースのtimestampをCarbonの日時オブジェクトとして扱える。

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
    // 一人のユーザーは複数の勤怠レコードを持つことができるという表記。

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    // 一人のユーザーは複数の修正申請を持つことができるという表記。
}
