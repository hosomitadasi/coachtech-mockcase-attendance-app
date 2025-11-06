<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreaksTable extends Migration
{

    public function up()
    {
        Schema::create('breaks', function (Blueprint $table) {
            // Schema::create('breaks')：データベースにbreaks(休憩)という名前のテーブルを新しく作る。
            // function (Blueprint $table)：$tableという「テーブルを設計するための変数」を使って、中にどんな項目を作るか作成していく。
            $table->id();
            // $table：テーブルを作るための設計図を扱う変数
            // id()：「id」という名前のカラムを自動生成。各休憩記録が何番化を識別するために使用。
            $table->foreignId('attendance_record_id')->constrained()->cascadeOnDelete();
            // foreignId('attendance_record_id')：どの勤怠記録に属する休憩化を示すためのID（外部キー）。attendance_recordsテーブルのidとつながる番号になる。constrained()で自動でattendance_records.idと紐づけし、cascadeOnDelete()で親関係にある勤怠記録が削除されたら関連する休憩データも同時に削除する処理を実施。
            $table->time('break_in');
            // time('break_in')：「break_in」という名前で 休憩開始時間を保存する time 型のカラムを追加。
            $table->time('break_out')->nullable();
            // time('break_out')：「break_out」という名前で 休憩終了時間のカラムを作る。nullable1で値がまだ無い状態（＝休憩中で終了していない状態）でも保存できるようにする。
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('breaks');
    }
}
