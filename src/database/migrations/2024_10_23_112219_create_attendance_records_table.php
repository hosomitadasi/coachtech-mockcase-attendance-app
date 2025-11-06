<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRecordsTable extends Migration
{

    public function up()
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            // IDで番号付け
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // どのユーザーの出勤記録か示す部分
            // foreignID：usersテーブルと繋がるキー。 constrained：自動的にusers.idとつなげる設定。 cascadeOnDelete：ユーザーが削除されたら、その人の勤怠記録も削除するルール。
            $table->date('date');
            // 出勤した日付を保存
            $table->time('clock_in');
            // 出勤した時間を保存
            $table->time('clock_out')->nullable();
            // 退勤した時間を保存。退勤していない場合もあるので、nullableで空でも保存できるようにする。
            $table->string('total_time')->nullable();
            // 1日の合計勤務時間を文字閉じて保存。
            $table->string('total_break_time')->nullable();
            // 合計休憩時間を文字として保存。
            $table->string('comment')->nullable();
            // 管理者や本人のメモなどを入れられる「備考」欄。
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_records');
    }
}
