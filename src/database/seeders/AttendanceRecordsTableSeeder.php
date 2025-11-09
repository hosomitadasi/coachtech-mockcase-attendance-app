<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;

class AttendanceRecordsTableSeeder extends Seeder
{
    public function run()
    {
        AttendanceRecord::factory()->count(10)->create();
        // 直訳すると「AttendanceRecord の工場（factory）を使って、10件のレコードを作ってデータベースに保存する」
        // factory：「サンプルデータの自動生成器」で、database/factoriesのファイルで、各カラムに何を入れるのか定義している。
        // count(10)->create()でランダムな勤怠情報が10件、データベースに追加されることを示す。
    }
}
