<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Application;
use App\Models\ApplicationBreak;
use App\Models\AttendanceRecord;
use App\Models\AttendanceBreak;
use App\Http\Requests\CorrectionRequest;

class UserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // 今ログインしているユーザーの情報を取得し$userに入れる。

        if ($user->attendance_status === '退勤済') {
            // ログイン中のユーザーの状態が「退勤中」なら、次の処理を実行。
            $attendance = AttendanceRecord::where('user_id', $user->id)
                ->whereDate('date', now()->format('Y-m-d'))
                ->first();
            // 今日の勤怠データがあるか検索
            // AttendanceRecord::where('user_id', $user->id)で勤怠テーブルから、ログインしたユーザーの勤怠を探す準備
            // whereDate('date', now()->format('Y-m-d'))で巨の日付の勤怠データだけに絞る。
            // firstで最初の１件（今日の勤怠）を取得する。
            // 上記処理が完了したら結果を$attendanceへ格納する。

            if (! $attendance) {
                $user->attendance_status = '勤務外';
                $user->save();
            }
            // $attendanceがnullなら状態を「勤務外」に変更し、データベースへ保存する。
        }

        $now = new \DateTime();
        // 現在の日時をまとめたものを作成し、$nowに保存。

        $week = [
            0 => '日', 1 => '月', 2 => '火', 3 => '水',
            4 => '木', 5 => '金', 6 => '土',
        ];
        // 0～6を日本語の曜日に変換するための配列。まとめて$weekに格納する。
        $weekday = $week[$now->format('w')];
        // 今日の曜日番号を取得しweekdayに格納する。$now->format('w')が今日の曜日番号であり、$week［］で日本語の曜日を取得する。
        $formattedDate = $now->format("Y年m月d日({$weekday})");
        // 今日の日付を表示用に作り直し、$formattedDateに格納する。{$weekday}で先ほど定義した曜日を表示できるようになる。
        $formattedTime = $now->format('H:i');
        // 現在時刻の内、時間と分だけ取り出す。

        return view(
            'user/attendance-register',
            compact('formattedDate', 'formattedTime', 'user')
        );
        // viewへ今日の日付（$formattedDate）と時間（formattedTime）、ログインしているユーザー（$user）の情報を渡す。
    }

    public function attendance(Request $request)
    {
        // ブラウザ上でどのボタンを押したかを受け取る入口。$requestの中にaction=clock_inなどの値が入ってくる。
        $user = Auth::user();
        // ここで現在ログインしているユーザーの情報をAuthデータベースから取得し$userに格納している。
        $action = $request->input('action');
        // フォームの<button name="action" value="clock_in"> の value を取り出す。どのボタンが押されたか知るために使う。

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();
        // AttendanceRecord::where('user_id', $user->id)：user_idはattendance_recordsテーブルのカラム名、$user->idは現在ログインしているユーザーのID。ここからデータベースAttendanceRecordの中でuser_id カラムと、ログイン中のユーザーの id が一致するデータだけを取得し$attendanceへ入れることを表す
        // whereDate('date', now()->toDateString())：whereDate('date', ...)がdate（出勤日）カラムの日付だけに注目して絞り込みをすること、now()->toDateString()が今日の日付を文字列で返すこと。ここからdateが今日の日付のデータだけを取得するということを表す。
        // first()：条件に合うデータを１件だけ取得する。見つからない場合はnullを返す。

        if (in_array($action, ['break_in','break_out','clock_out']) && ! $attendance) {
            return redirect('/attendance')->withErrors('出勤していません。先に「出勤」をしてください。');
        }
        // 休憩ボタンや退勤ボタンを押して誤動作が起きないようにする安全装置。

        // ユーザーば押したボタンに応じて勤怠レコードの更新、休憩レコードの更新。またattendance_statusの更新。
        if ($action === 'clock_in' && $user->attendance_status === '勤務外') {
            // 出勤前の状態で「出勤」ボタン押したときの処理。
            $attendance             = new AttendanceRecord();
            // 新しい勤怠レコード（AttendanceRecord の空オブジェクト）を作成。
            $attendance->user_id    = $user->id;
            // この勤怠レコードが「どのユーザーのものか」を設定。attendance_records.user_id にログイン中のユーザーIDをセット。
            $attendance->date       = now();
            // 今日の日付をセット。
            $attendance->clock_in   = Carbon::now();
            // 出勤時刻（clock_in）に現在時刻を保存。
            $attendance->save();
            //ここで DB に INSERT 実行。

            $user->attendance_status = '出勤中';
            $user->save();
            // ユーザーが今「出勤中」になったことを DB に保存。

        } elseif ($action === 'break_in' && $user->attendance_status === '出勤中') {
            // 出勤中の人が「休憩入」ボタンを押した場合の処理。
            $attendance->breaks()->create([
                'break_in' => Carbon::now(),
            ]);
            // $attendance->breaks() は AttendanceRecord → AttendanceBreak のリレーションを表す。
            // 新しい休憩レコードを作成し、break_in に現在時刻を保存する（break_outをnullのまま作成される）。

            $user->attendance_status = '休憩中';
            $user->save();
            // statusを「休憩中」に変更。

        } elseif ($action === 'break_out' && $user->attendance_status === '休憩中') {
            // 休憩戻りかつ休憩中の時
            $currentBreak = $attendance
                ->breaks()
                ->whereNull('break_out')
                ->latest()
                ->first();
            // $attendance->breaks()はさっきと同じで休憩情報からwhereNull('break_out')（whereNull＝入力されていない）＝休憩終了時間がまだ入力されていないレコードを
            // latest()=時間順で新しいものを
            // first();=１件だけ取得する

            if ($currentBreak) {
                $currentBreak->break_out = Carbon::now();
                $currentBreak->save();
            }
            // 休憩終了時刻を保存すること。

            $user->attendance_status = '出勤中';
            $user->save();
            // ステータスを出勤中に変更すること。

        } elseif ($action === 'clock_out' && $user->attendance_status === '出勤中') {
            // 退勤しかつ出勤中の時。
            $attendance->clock_out = Carbon::now();

            $clockIn  = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);

            $totalBreakTime = 0;
            foreach ($attendance->breaks as $b) {
                if ($b->break_in && $b->break_out) {
                    $totalBreakTime +=
                        Carbon::parse($b->break_in)
                            ->diffInMinutes(Carbon::parse($b->break_out));
                }
            }

            $attendance->total_break_time = sprintf(
                '%02d:%02d',
                floor($totalBreakTime/60),
                $totalBreakTime%60
            );

            $workedMins = $clockIn->diffInMinutes($clockOut) - $totalBreakTime;
            $attendance->total_time = sprintf(
                '%02d:%02d',
                floor($workedMins/60),
                $workedMins%60
            );

            $attendance->save();

            $user->attendance_status = '退勤済';
            $user->save();
        }

        return redirect('/attendance');
        // 処理が完了したら/attendance「打刻画面」へ結果を返す。
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::parse($request->query('date', now()));
        // URLにあるdateパラメータを読み、何も指定されていない場合はnow()が利用される。Carbon::parseは文字列や日付をCarbonに変換する機能を持つ。

        $startOfMonth = $date->copy()->startOfMonth();
        // $date の 月初（1日） を表す Carbon オブジェクトをつくる。コピーしているのは元のデータを壊さないようにするため。
        $endOfMonth   = $date->copy()->endOfMonth();
        // こちらは月末の日付を取得する。これにより$data = 2025-01-12ならstartOFMonthで2025/01/01が、endOFMonthで2025/01/31が取得される。

        $attendanceRecords = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $formatted = $attendanceRecords->map(function ($rec) {
            $weekdays = ['日','月','火','水','木','金','土'];
            $d = Carbon::parse($rec->date);
            return [
                'id'               => $rec->id,
                'date'             => $d->format('m/d') . "({$weekdays[$d->dayOfWeek]})",
                'clock_in'         => $rec->clock_in  ? Carbon::parse($rec->clock_in)->format('H:i') : null,
                'clock_out'        => $rec->clock_out ? Carbon::parse($rec->clock_out)->format('H:i') : null,
                'total_time'       => $rec->total_time,
                'total_break_time' => $rec->total_break_time,
            ];
        });

        return view('user/user-attendance-list', [
            'formattedAttendanceRecords' => $formatted,
            'date'      => $date,
            'nextMonth'=> $date->copy()->addMonth()->format('Y-m'),
            'previousMonth'=> $date->copy()->subMonth()->format('Y-m'),
        ]);
    }

    public function detail($id)
    {
        // コントローラのdetailメソッド定義。$idはURLパラメータ（detail/5の「5」）
        $attendanceRecord = AttendanceRecord::findOrFail($id);
        // AttendanceRecordモデルから、主キー（id）でレコードを取得。見つからなかった場合404エラーを自動的に返す。
        $user = Auth::user();
        // 現在ログインしているユーザーを取得する。

        // マスター休憩（実際に記録されている休憩）の取得処理
        $masterBreaks = $attendanceRecord->breaks()->get()->map(function ($b) {
            return [
                'break_in'  => $b->break_in ? Carbon::parse($b->break_in)->format('H:i') : null,
                'break_out' => $b->break_out ? Carbon::parse($b->break_out)->format('H:i') : null,
            ];
        })->toArray();

        // 未承認申請の修正申請があるか確認する処理。
        $application = Application::where('attendance_record_id', $id)
            ->where('approval_status', '承認待ち')
            ->first();
        $proposal = [];
        if ($application) {
            $proposal = $application->proposalBreaks()->get()->map(function ($b) {
                return [
                    'break_in'  => Carbon::parse($b->break_in)->format('H:i'),
                    'break_out' => $b->break_out ? Carbon::parse($b->break_out)->format('H:i') : null,
                ];
            })->toArray();
        }

        $data = [
            'id'            => $attendanceRecord->id,
            'year'          => $attendanceRecord->date
                                ? Carbon::parse($attendanceRecord->date)->format('Y年')
                                : null,
            'date'          => $attendanceRecord->date
                                ? Carbon::parse($attendanceRecord->date)->format('m月d日')
                                : null,
            'clock_in'      => $attendanceRecord->clock_in
                                ? Carbon::parse($attendanceRecord->clock_in)->format('H:i')
                                : null,
            'clock_out'     => $attendanceRecord->clock_out
                                ? Carbon::parse($attendanceRecord->clock_out)->format('H:i')
                                : null,
            'breaks'        => $masterBreaks,
            'proposal_breaks'=> $proposal,
            'comment'       => $attendanceRecord->comment,
            'application'   => $application,
        ];

        return view('user/user-detail', compact('user', 'data'));
    }

    public function amendmentApplication(CorrectionRequest $request, $id)
    {
        $user = Auth::user();
        $application = Application::create([
            'user_id'              => $user->id,
            'attendance_record_id' => $id,
            'approval_status'      => '承認待ち',
            'application_date'     => now(),
            'new_date'             => Carbon::createFromFormat('n月j日', $request->new_date)
                                        ->year(now()->year)
                                        ->format('Y-m-d'),
            'new_clock_in'         => Carbon::parse($request->new_clock_in)->format('H:i'),
            'new_clock_out'        => Carbon::parse($request->new_clock_out)->format('H:i'),
            'comment'              => $request->comment,
        ]);

        // 申請用休憩を作成
        $rawIns  = (array) $request->input('new_break_in', []);
        $rawOuts = (array) $request->input('new_break_out', []);
        $pairs = [];
        foreach (array_values(array_filter($rawIns)) as $i => $in) {
            $out = array_values(array_filter($rawOuts))[$i] ?? null;
            $pairs[] = [
                'break_in'  => Carbon::parse($in)->format('H:i'),
                'break_out' => $out ? Carbon::parse($out)->format('H:i') : null,
            ];
        }
        $application->proposalBreaks()->createMany($pairs);

        return redirect('/stamp_correction_request/list');
    }

    public function applicationList()
    {
        $user         = Auth::user();
        $applications = Application::where('user_id', $user->id)->get();

        // 一覧表示のために必要なデータを取得
        $formattedApplications = $applications->map(function ($application) {
            return [
                'id'            => $application->id,
                'application_date' => $application->application_date
                                ? Carbon::parse($application->application_date)->format('Y/m/d')
                                : null,
                'date'          => $application->new_date,
                'clock_in'      => $application->new_clock_in,
                'clock_out'     => $application->new_clock_out,
                'comment'       => $application->comment,
                'approval_status' => $application->approval_status,
            ];
        });

        return view(
            'user/user-application-list',
            compact('user', 'formattedApplications')
        );
    }

    public function applicationDetail($id)
    {
        $user = Auth::user();
        $application = Application::findOrFail($id);

        $proposalBreaks = $application->proposalBreaks()->get()->map(function ($b) {
            return [
                'break_in'  => $b->break_in ? Carbon::parse($b->break_in)->format('H:i') : null,
                'break_out' => $b->break_out ? Carbon::parse($b->break_out)->format('H:i') : null,
            ];
        })->toArray();

        $data = [
            'id'            => $application->id,
            'year'          => $application->new_date
                                ? Carbon::parse($application->new_date)->format('Y年')
                                : null,
            'date'          => $application->new_date
                                ? Carbon::parse($application->new_date)->format('m月d日')
                                : null,
            'clock_in'      => $application->new_clock_in
                                ? Carbon::parse($application->new_clock_in)->format('H:i')
                                : null,
            'clock_out'     => $application->new_clock_out
                                ? Carbon::parse($application->new_clock_out)->format('H:i')
                                : null,
            'breaks'        => $proposalBreaks,
            'comment'       => $application->comment,
            'application'   => $application,
        ];

        return view('user/user-detail', compact('user', 'data'));
    }
}
