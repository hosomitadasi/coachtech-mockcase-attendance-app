<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRecord;
use App\Models\AttendanceBreak;
use Faker\Factory as Faker;
use Carbon\Carbon;

class BreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        AttendanceRecord::all()->each(function (AttendanceRecord $record) use ($faker) {
            $clockIn  = Carbon::parse($record->clock_in);
            $clockOut = Carbon::parse($record->clock_out ?? $record->clock_in->copy()->addHours(8));

            $breakCount = rand(1, 2);

            for ($i = 0; $i < $breakCount; $i++) {
                $in = $faker->dateTimeBetween($clockIn->toDateTimeString(), $clockOut->toDateTimeString());
                $out = $faker->dateTimeBetween($in, $clockOut->toDateTimeString());

                AttendanceBreak::create([
                    'attendance_record_id' => $record->id,
                    'break_in'  => Carbon::instance($in)->format('H:i:s'),
                    'break_out' => Carbon::instance($out)->format('H:i:s'),
                ]);
            }
        });
    }
}
