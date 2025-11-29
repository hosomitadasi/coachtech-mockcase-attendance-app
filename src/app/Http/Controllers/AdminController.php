<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\Application;
use Carbon\Carbon;
use App\Http\Requests\CorrectionRequest;

class AdminController extends Controller
{
    public function list(Request $request)
    {
        // HTTPリクエスト（ブラウザから来る情報）を受け取る部分。$requestに「今日見ている日付」など、ブラウザから送られた情報が入る。

        $users = User::all();
        // データベースのuserテーブルに登録されている全てのユーザー情報（全行）を取得して、変数$usersに入れる。
        // 画面に社員一覧を出したり、誰の勤怠を表示するかの「対象ユーザー一覧」を作るために使う。

        $date = Carbon::parse($request->query('date', Carbon::now()));
        // $request->query('date', Carbon::now())はブラウザのURLに?date=2025-11-12 のように指定があればその値を取る。なければ Carbon::now()（＝現在日時）をデフォルトで使う。
        // Carbon::parse(...)は、文字列（例 "2025-11-12"）や日付オブジェクトを Carbon（Laravelでよく使う日時の便利オブジェクト）に変換する。
        // 上記内容から、$date は「見たい日」を表す Carbon オブジェクトになる。URLに?date=2025-11-12 があれば $date は 2025年11月12日 を表す Carbon。何もなければ今の日時。

        $attendanceRecords = AttendanceRecord::whereDate('date', $date)->whereIn('user_id', $users->pluck('id'))->get();
        // 指定の日に出勤記録がある場合はそれを全ユーザーの勤怠記録から引っ張ってきて$attendanceRecordsに入れている。
        // AttendanceRecord::whereDate('date', $date)のwhereDate は「dateカラムの日付部分が指定の日と同じものを探す」命令。$dateに入っているCarbon を渡すことでLaravelが適切に日付比較を実行する。
        // whereIn('user_id', $users->pluck('id'))のwhereIn は「user_id が指定した列の中に含まれているレコードだけに絞る」命令。$users->pluck('id')で$users（ユーザー全員）から「idだけ」を抜き出したリストを作成する。この組み合わせで、「その日かつユーザー一覧に含まれる人の勤怠」を取得する。
        // 最後にgetを実行して結果を取得して$attendanceRecordsに格納する。

        return view('admin/admin-attendance-list', [
            'users' => $users,
            'attendanceRecords' => $attendanceRecords,
            'date' => $date,
            'previousDay' => $date->copy()->subDay()->format('Y-m-d'),
            'nextDay' => $date->copy()->addDay()->format('Y-m-d'),
        ]);
        // resources/views/admin/admin-attendance-list.blade.phpに結果を表示する処理。
        // 'users' => $usersは、users という名前で $users の中身を使えるように渡している。view内でusersと出ればデータベース上のユーザーの名前が画像に表示される。
        // 'attendanceRecords' => $attendanceRecordsでその日分の勤怠データ一覧を渡している。
        // 'date' => $dateで表示している「日付」をそのまま渡す。
        // 'previousDay' => $date->copy()->subDay()->format('Y-m-d')は、$date->copy() で $date（Carbonオブジェクト）のコピーを作成（元の$dataを壊さないため）し、->subDay() で1日前に移動。
        // ->format('Y-m-d') で 2025-11-11 のような文字列に変換するといった作業を実施。これで前日へと移動するリンクを作成。'nextDay'は翌日となる。
    }
    // ある１日の勤怠一覧画面を表示する機能

    public function staffList()
    {
        $users = User::all();
        // $usersにUser（登録されたスタッフ）のすべての情報を格納する。

        return view('admin/staff-list', compact('users'));
        // admin/staff-listへ上記userの情報を渡し表示できるようにする。
    }

    public function staffDetailList(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $date = Carbon::parse($request->query('date', Carbon::now()));

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $attendanceRecords = AttendanceRecord::where('user_id', $user->id)->whereBetween('date', [$startOfMonth, $endOfMonth])->get();

        $formattedAttendanceRecords = $attendanceRecords->map(function ($attendance) {
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            $date = Carbon::parse($attendance->date);
            $weekday = $weekdays[$date->dayOfWeek];
            return [
                'id' => $attendance->id,
                'date' => $date->format('m/d') . "($weekday)",
                'clock_in' => $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : null,
                'clock_out' => $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : null,
                'total_time' => $attendance->total_time,
                'total_break_time' => $attendance->total_break_time
            ];
        });

        return view(
            'admin/staff-attendance-list',
            [
                'user' => $user,
                'date' => $date,
                'formattedAttendanceRecords' => $formattedAttendanceRecords,
                'previousMonth' => $date->copy()->subMonth()->format('Y-m'),
                'nextMonth' => $date->copy()->addMonth()->format('Y-m')
            ]
        );
    }

    public function detail($id)
    {
        $attendanceRecords = AttendanceRecord::with('breaks')->findOrFail($id);
        $user = User::findOrFail($attendanceRecords->user_id);

        $application = Application::where('attendance_record_id', $attendanceRecords->id)
            ->where('approval_status', '承認待ち')->get();

        // --- breaksテーブルから取得 ---
        $breaks = $attendanceRecords->breaks->map(function ($break) {
            return [
                'break_in' => $break->break_in ? Carbon::parse($break->break_in)->format('H:i') : '',
                'break_out' => $break->break_out ? Carbon::parse($break->break_out)->format('H:i') : '',
            ];
        })->toArray();

        $attendanceRecord = [
            'application' => $attendanceRecords->application,
            'id' => $attendanceRecords->id,
            'year' => $attendanceRecords->date ? Carbon::parse($attendanceRecords->date)->format('Y年') : null,
            'date' => $attendanceRecords->date ? Carbon::parse($attendanceRecords->date)->format('m月d日') : null,
            'clock_in' => $attendanceRecords->clock_in ? Carbon::parse($attendanceRecords->clock_in)->format('H:i') : null,
            'clock_out' => $attendanceRecords->clock_out ? Carbon::parse($attendanceRecords->clock_out)->format('H:i') : null,
            'breaks' => $breaks,
            'comment' => $attendanceRecords->comment,
        ];

        return view('admin.admin-detail', compact('user', 'attendanceRecord', 'application'));
    }

    public function amendmentApplication(CorrectionRequest $request, $id)
    {
        $attendance = AttendanceRecord::findOrFail($id);
        $user = User::findOrFail($attendance->user_id);

        // --- 日付変換（配列で来る場合にも対応） ---
        $dateString = $request->new_date;
        if (is_array($dateString)) {
            $dateString = $dateString[1];
        }
        $parsedDate = Carbon::createFromFormat('n月j日', $dateString)->year(now()->year)->format('Y-m-d');
        $attendance->date = $parsedDate;

        // --- 出退勤時間の更新 ---
        $attendance->clock_in = Carbon::parse($request->new_clock_in)->format('H:i');
        $attendance->clock_out = Carbon::parse($request->new_clock_out)->format('H:i');

        // --- 備考の保存 ---
        $attendance->comment = $request->comment;
        $attendance->save();

        // --- 休憩時間の更新 ---
        $attendance->breaks()->delete(); // 既存休憩を削除

        $breakIns = $request->input('new_break_in', []);
        $breakOuts = $request->input('new_break_out', []);
        $totalBreakMinutes = 0;

        for ($i = 0; $i < count($breakIns); $i++) {
            if (!empty($breakIns[$i]) && !empty($breakOuts[$i])) {
                $breakIn = Carbon::parse($breakIns[$i])->format('H:i');
                $breakOut = Carbon::parse($breakOuts[$i])->format('H:i');

                $attendance->breaks()->create([
                    'break_in' => $breakIn,
                    'break_out' => $breakOut,
                ]);

                $totalBreakMinutes += Carbon::parse($breakOut)->diffInMinutes(Carbon::parse($breakIn));
            }
        }

        // --- 勤務時間・休憩時間の計算 ---
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);
        $totalWorkedMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;

        $attendance->total_break_time = sprintf('%02d:%02d', intdiv($totalBreakMinutes, 60), $totalBreakMinutes % 60);
        $attendance->total_time = sprintf('%02d:%02d', intdiv($totalWorkedMinutes, 60), $totalWorkedMinutes % 60);
        $attendance->save();

        return app(AdminController::class)->detail($id);
    }


    public function applicationList()
    {
        $user = User::all();
        $applications = Application::all();
        return view('admin/admin-application-list', compact('user', 'applications'));
    }

    public function approvalShow($id)
    {
        $application = Application::findOrFail($id);
        $user = User::findOrFail($application->user_id);

        $application->new_date = Carbon::parse($application->new_date);
        $application->new_clock_in = $application->new_clock_in ? Carbon::parse($application->new_clock_in)->format('H:i') : null;
        $application->new_clock_out = $application->new_clock_out ? Carbon::parse($application->new_clock_out)->format('H:i') : null;
        $application->new_break_in = $application->new_break_in ? Carbon::parse($application->new_break_in)->format('H:i') : null;
        $application->new_break_out = $application->new_break_out ? Carbon::parse($application->new_break_out)->format('H:i') : null;

        return view('admin/admin-application-detail', compact('user', 'application'));
    }

    public function approval(Request $request, $id)
{
    $application = Application::findOrFail($id);
    $user = User::findOrFail($application->user_id);
    $attendanceRecord = AttendanceRecord::findOrFail($application->attendance_record_id);

    $application->approval_status = "承認済み";
    $application->save();

    $attendanceRecord->date = $application->new_date;
    $attendanceRecord->clock_in = $application->new_clock_in;
    $attendanceRecord->clock_out = $application->new_clock_out;
    $attendanceRecord->comment = $application->comment;

    // 既存のbreaksを削除
    $attendanceRecord->breaks()->delete();

    // application_breaks → breaksへコピー
    foreach ($application->proposalBreaks as $applicationBreak) {
        $attendanceRecord->breaks()->create([
            'break_in' => $applicationBreak->break_in,
            'break_out' => $applicationBreak->break_out,
        ]);
    }

    $clockIn = Carbon::parse($attendanceRecord->clock_in);
    $clockOut = Carbon::parse($attendanceRecord->clock_out);

    $totalBreakTime = 0;
    foreach ($attendanceRecord->breaks as $break) {
        if ($break->break_in && $break->break_out) {
            $breakIn = Carbon::parse($break->break_in);
            $breakOut = Carbon::parse($break->break_out);
            $totalBreakTime += $breakIn->diffInMinutes($breakOut);
        }
    }

    $totalBreakHours = floor($totalBreakTime / 60);
    $totalBreakMinutes = $totalBreakTime % 60;
    $attendanceRecord->total_break_time = sprintf('%02d:%02d', $totalBreakHours, $totalBreakMinutes);

    $totalWorkedMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakTime;
    $hours = floor($totalWorkedMinutes / 60);
    $minutes = $totalWorkedMinutes % 60;
    $attendanceRecord->total_time = sprintf('%02d:%02d', $hours, $minutes);

    $attendanceRecord->save();

    return app(AdminController::class)->applicationList($id);
}

    public function export(Request $request)
    {
        $userId = $request->input('user_id');
        $yearMonth = $request->input('year_month');
        $startDate = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        $staffAttendance = AttendanceRecord::where('user_id', $userId)->whereBetween('date', [$startDate, $endDate])->get();

        $user = User::find($userId);
        $userName = $user->name;


        $csvHeader = [
            '日付',
            '出勤時間',
            '退勤時間',
            '休憩時間',
            '勤務時間'
        ];
        $temps = [];
        array_push($temps, $csvHeader);

        foreach ($staffAttendance as $staff) {
            $temp = [
                Carbon::parse($staff->date)->format('Y/m/d'),
                Carbon::parse($staff->clock_in)->format('H:i'),
                Carbon::parse($staff->clock_out)->format('H:i'),
                $staff->total_break_time,
                $staff->total_time
            ];
            array_push($temps, $temp);
        }
        $stream = fopen('php://temp', 'r+b');
        foreach ($temps as $temp) {
            fputcsv($stream, $temp);
        }
        rewind($stream);
        $csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $filename = "{$userName}さんの勤怠リスト.csv";
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        );
        return response($csv, 200, $headers);
    }
}
