<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return 'Hello World';
});

// 管理者ログイン
Route::get('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('adminauth');
// 会員登録画面（一般ユーザー）
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
// ログイン画面（一般ユーザー）
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// 共通ルート：ユーザーでも管理者でも入れるようにする
Route::get('/stamp_correction_request/approve/{correction}', [StampCorrectionRequestController::class, 'editApprove'])
    ->name('stamp_correction_request.edit_approve')
    ->middleware(['web']); // 最低限のミドルウェア（必要に応じてauthなど調整）

Route::get('email/verify', [AuthController::class, 'verify'])->name('verification.notice');
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('email/resend', [AuthController::class, 'resendVerification'])->name('verification.resend');

// 申請一覧画面

Route::name('user.')->middleware(['auth', 'verified'])->group(function () {
    // 出勤登録画面（一般ユーザー）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::post('/attendance/stamp', [AttendanceController::class, 'stamp'])->name('attendance.stamp');

    // 勤怠一覧画面（一般ユーザー）
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

});

Route::middleware(['auth.any'])->group(
    function () {
        Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');
        Route::get('/attendance/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
        Route::post('/attendance/update', [AttendanceController::class, 'update'])->name('attendance.update');
    }
);

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('admin/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        // 勤怠一覧画面（管理者）
        Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('attendance.list');

        // スタッフ一覧画面（管理者）
        Route::get('/attendance/staff_list', [UserController::class, 'staffList'])->name('attendance.staff_list');

        // スタッフ別勤怠一覧画面（管理者）
        Route::get('/attendance/staff/{user_id}', [AdminAttendanceController::class, 'staffAttendance'])->name('attendance.staff');

        // スタッフ別勤怠CSV出力（管理者）
        Route::get('/attendance/staff/{user_id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('attendance.staff.csv');

        // 修正申請承認画面（管理者）
        Route::post('/stamp_correction_request/approve/{correction}', [StampCorrectionRequestController::class, 'approve'])
            ->name('stamp_correction_request.approve');
    });
});
