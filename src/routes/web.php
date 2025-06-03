<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\UserController;
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

// 管理者ログイン
Route::get('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('adminauth');
//会員登録画面（一般ユーザー）
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
//ログイン画面（一般ユーザー）
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');


Route::get('email/verify', [AuthController::class, 'verify'])->name('verification.notice');
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('email/resend', [AuthController::class, 'resendVerification'])->name('verification.resend');

Route::name('user.')->middleware(['auth', 'verified'])->group(function () {
    //出勤登録画面（一般ユーザー）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::post('/attendance/stamp', [AttendanceController::class, 'stamp'])->name('attendance.stamp');


    //勤怠一覧画面（一般ユーザー）
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    //勤怠詳細画面（一般ユーザー）
    Route::get('/attendance/{attendance_id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    //申請一覧画面（一般ユーザー）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('store');
});


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        //勤怠一覧画面（管理者）
        Route::get('attendance/list', [AdminAttendanceController::class, 'list'])->name('attendance.list');
        // パスは admin/attendance/list となるので prefixのadminと連結される

        //勤怠詳細画面（管理者）
        Route::get('attendance/{attendance_id}', [AdminAttendanceController::class,  'detail'])->name('attendance.detail');

        //スタッフ一覧画面（管理者）
        Route::get('staff/list', [UserController::class, 'staffList'])->name('staff.list');

        //スタッフ別勤怠一覧画面（管理者）
        Route::get('attendance/staff/{user_id}', [AdminAttendanceController::class, 'staffAttendance'])->name('admin.attendance.staff');

        //申請一覧画面（管理者）
        Route::get('stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');

        //修正申請承認画面（管理者）
        Route::get('stamp_correction_request/approve/{attendance_correct_request}', [StampCorrectionRequestController::class, 'editApprove'])->name('stamp_correction_request.approve');
    });
});
