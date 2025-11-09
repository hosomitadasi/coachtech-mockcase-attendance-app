<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToUsersTable extends Migration
{

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('admin_status')->default(false);
            // 管理者かユーザーなのかの特定に使用。admin_statusという真偽値カラムを追加し、trueなら管理者、falseなら一般ユーザーと指定。default(false)により、登録時は一般ユーザーとして扱われる。
            $table->string('attendance_status')->default('勤務外');
            // 勤怠の現在ステータスを保存するカラム。default により新規登録時は必ず 「勤務外」 からスタートするようになっている。
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
