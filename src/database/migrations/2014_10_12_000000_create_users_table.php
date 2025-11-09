<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // usersテーブルの主キー。他への１対１、１対多数の関係時にこの番号が使用される。
            $table->string('name');
            // 名前を文字カラムで保存する。
            $table->string('email')->unique();
            // メールアドレスを文字カラムで保存する。uniqueを使用することにより同じメールが重複して登録されないようにする。
            $table->timestamp('email_verified_at')->nullable();
            // メール認証が完了した日時を保存する。登録直後は認証前となるためnullableで空を保存できるようにすることで、認証前の状態で保存できるようにする。
            $table->string('password');
            // パスワードを保存する。
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
