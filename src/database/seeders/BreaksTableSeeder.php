<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRecord;
use App\Models\AttendanceBreak;
use Faker\Factory as Faker;
// ダミーデータ（ランダムな日時や文字列）を作るツール
use Carbon\Carbon;

class BreaksTableSeeder extends Seeder
{

    public function run()
    {
        $faker = Faker::create();
        // Fakerのインスタンスを作成する。これでランダムな日時を生成する。

        AttendanceRecord::all()->each(function (AttendanceRecord $record) use ($faker) {
            // AttendanceRecord::all()：データベースの全ての勤怠レコードを取り出す（配列のように扱える集合で返る）
            // each(function () {})：取り出した各勤怠レコードに対して順番に中の処理を実行する。
            $clockIn  = Carbon::parse($record->clock_in);
            $clockOut = Carbon::parse(
                $record->clock_out ?? $clockIn->copy()->addHours(8)
            );
            // Carbon::parse(...)：データベースに保存された文字列（例："09:00:00"）を Carbon の日時オブジェクトに変換。
            // $record->clock_out ?? $clockIn->copy()->addHours(8)：「もし退勤（clock_out）が設定されていればそれを使い、なければ出勤時刻に8時間足した時間を退勤とみなす」

            // 万一 start > end なら入れ替え
            if ($clockIn->gt($clockOut)) {
                [$clockIn, $clockOut] = [$clockOut, $clockIn];
            }

            $breakCount = rand(0, 5);
            // rand(0, 5)：PHPの関数で、0～5のランダムに休憩した回数を選ぶ。

            for ($i = 0; $i < $breakCount; $i++) {
                // ループは決めた回数だけ繰り返す（休憩を1件ずつ作る）。
                $startStr = $clockIn->format('Y-m-d H:i:s');
                $endStr   = $clockOut->format('Y-m-d H:i:s');
                // $startStr と $endStr は、出勤と退勤の「日時の文字列」形式。Faker に渡すために使う。
                $in = $faker->dateTimeBetween($startStr, $endStr);
                $inStr = $in->format('Y-m-d H:i:s');
                // 出勤〜退勤の間でランダムな休憩開始時刻を作る。
                $out   = $faker->dateTimeBetween($inStr, $endStr);
                // 休憩開始時刻から退勤時刻の間でランダムな休憩終了時刻を作る。これで休憩の開始 < 終了を保証する。

                AttendanceBreak::create([
                    // 休憩テーブルに1件レコードを追加する命令。配列の中身は実際に保存するカラムと値になる。
                    'attendance_record_id' => $record->id,
                    'break_in'             => Carbon::instance($in)->format('H:i:s'),
                    'break_out'            => Carbon::instance($out)->format('H:i:s'),
                ]);
            }
        });
    }
}
