<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationBreaksTable extends Migration
{

    public function up()
    {
        Schema::create('application_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                ->constrained('applications')
                ->cascadeOnDelete();
            // どの申請の休憩化を紐づけるID。
            $table->time('break_in');
            // 修正後の休憩開始時刻を格納。
            $table->time('break_out')->nullable();
            // 修正後の休憩終了時刻を格納（ない場合は空のまま）
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_breaks');
    }
}
