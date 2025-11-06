<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{

    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_record_id')->constrained()->cascadeOnDelete();
            // attendance_recordは各勤怠のIDであり、これと紐づけることでどの勤怠情報の修正かを確認する。
            $table->string('approval_status');
            // 申請状態を保存。今回は承認待ち（pending）または承認済み（approved）となる。
            $table->date('application_date');
            // 申請を提出した日付を格納
            $table->date('new_date');
            // 修正後の勤怠日付を保存。
            $table->time('new_clock_in');
            // 修正後の出勤時刻を格納。
            $table->time('new_clock_out');
            // 修正後の退勤時刻を格納。
            $table->string('comment');
            // 修正理由を格納。
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('applications');
    }
}
