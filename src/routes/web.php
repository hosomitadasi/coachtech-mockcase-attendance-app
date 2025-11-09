<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MiddlewareController;
use App\Http\Middleware\AdminStatusMiddleware;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Illuminate\Http\Request;
use App\Http\Requests\CorrectionRequest;

Route::middleware('auth')->group(function () {
    // 一般ユーザーとしてログインしていないと入れないエリア。
    Route::get('/attendance', [UserController::class, 'index']);
    // UserControllerのindexを呼び出し、/attendance「打刻画面」を出力する。
    Route::post('/attendance', [UserController::class, 'attendance']);
    // UserControllerのattendanceを呼び出し。打刻ボタンを押したときの動作処理を実施。
    Route::get('/attendance/list', [UserController::class, 'list']);
    // UserControllerのlistを呼び出し、/attendance/list「勤怠一覧画面」を出力する。
    Route::get('/application/{id}', [UserController::class, 'applicationDetail']);
    // UserControllerのapplicationDetailを呼び出し、/application/{id}「修正一覧画面」を出力する。。
});

Route::middleware(['auth'])->group(function () {
    // ログインが必要な場所。admin_statusのチェックはまだない。
    Route::get('/admin/attendance/list', [AdminController::class, 'list']);
    // AdminControllerのlistを呼び出し、/admin/attendance/list「管理者用勤怠一覧画面」を出力する。
    Route::get('/admin/staff/list', [AdminController::class, 'staffList']);
    // AdminControllerのstaffListを呼び出し、/admin/staff/list「スタッフ一覧画面」を出力する。
    Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'staffDetailList']);
    // AdminControllerのstaffDetailListを呼び出し、/admin/attendance/staff/{id}「特定スタッフの勤務履歴画面」を出力する。
    Route::post('/admin/logout', [AuthController::class, 'adminLogout']);
    // AuthControllerのadminLogoutを呼び出し、ログアウト処理を実施する。
    Route::get('/stamp_correction_request/approve/{id}', [AdminController::class, 'approvalShow']);
    // AdminControllerのapprovalShowを呼び出し、/stamp_correction_request/approve/{id}「修正申請の承認画面」を出力する。
    Route::post('/stamp_correction_request/approve/{id}', [AdminController::class, 'approval']);
    // AdminControllerのapprovalを呼び出し、修正した勤怠情報を承認する処理を実施する。
    Route::post('/export', [AdminController::class, 'export']);
    // AdminControllerのexportの呼び出し、CSV などのデータを出力する。
});

Route::middleware(['auth', AdminStatusMiddleware::class])->group(function () {
    Route::get('/stamp_correction_request/list', function (Request $request) {
        if ($request->headers->has('referer') && str_contains($request->headers->get('referer'), '/admin')) {
            if (auth()->user()->admin_status) {
                return app(AdminController::class)->applicationList($request);
            }
        } else {
            return app(UserController::class)->applicationList($request);
        }
    });
    Route::get('/attendance/{id}', function ($id, Request $request) {
        if ($request->headers->has('referer') && str_contains($request->headers->get('referer'), '/admin')) {
            if (auth()->user()->admin_status) {
                return app(AdminController::class)->detail($id);
            }
        } else {
            return app(UserController::class)->detail($id);
        }
    });
    Route::post('/attendance/{id}', function (CorrectionRequest $request, $id) {
        if (auth()->user()->admin_status) {
            if (auth()->user()->admin_status) {
                return app(AdminController::class)->amendmentApplication($request, $id);
            }
        } else {
            return app(UserController::class)->amendmentApplication($request, $id);
        }
    });
});

Route::get('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/admin/login', [AuthController::class, 'adminDoLogin']);
Route::post('/login', [AuthController::class, 'doLogin']);
Route::post('/logout', [AuthController::class, 'doLogout']);
Route::post('/register', [AuthController::class, 'store']);
// それぞれログイン、会員登録、ログアウトの処理を実施する。

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware(['auth'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed'])
    ->name('verification.verify');
