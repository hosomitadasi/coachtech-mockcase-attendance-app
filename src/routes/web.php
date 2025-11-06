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


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth')->group(function () {
    Route::get('/attendance', [UserController::class, 'index']);
    // 勤怠打刻画面の表示
    Route::post('/attendance', [UserController::class, 'attendance']);
    // 勤怠打刻処理
    Route::get('/attendance/list', [UserController::class, 'list']);
    // 各ユーザーの勤怠一覧の表示
    Route::get('/application/{id}', [UserController::class, 'applicationDetail']);
    // 修正申請の詳細確認
});
// 一般ユーザーの勤怠処理機能

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'list']);
    // ある１日の勤怠一覧表示
    Route::get('/admin/staff/list', [AdminController::class, 'staffList']);
    // 登録されたスタッフの一覧表示
    Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'staffDetailList']);
    // 各スタッフの月次一覧表示
    Route::post('/admin/logout', [AuthController::class, 'adminLogout']);
    // 管理者ログアウト機能
    Route::get('/stamp_correction_request/approve/{id}', [AdminController::class, 'approvalShow']);
    // 修正申請承認画面の表示
    Route::post('/stamp_correction_request/approve/{id}', [AdminController::class, 'approval']);
    // 承認処理
    Route::post('/export', [AdminController::class, 'export']);
    // 勤怠データのエクスポート
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
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware(['auth'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed'])
    ->name('verification.verify');
