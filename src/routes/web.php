<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\CorrectionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

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

//メール認証誘導画面へ
Route::get('/email/verify', function () {
    return view('auth.verify_email');
})->middleware('auth')->name('verification.notice');
//メール認証ボタン
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $user = Auth::user();
    if ($user->authority === 'admin') {
        return redirect()->route('admin.attendance.list');
    } else {
        return redirect()->route('attendance');
    }
})->middleware(['auth', 'signed'])->name('verification.verify');
//メール再送ボタン
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back();
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware(['auth', 'verified'])->group(function () {
    // 勤怠・休憩打刻
    Route::get('/attendance', [AttendanceController::class,'attendance'])->name('attendance');
    Route::post('/attendance/start', [AttendanceController::class,'attendanceStart'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class,'attendanceEnd'])->name('attendance.end');
    Route::post('/rest/start', [AttendanceController::class,'restStart'])->name('rest.start');
    Route::post('/rest/end', [AttendanceController::class,'restEnd'])->name('rest.end');
    // 勤怠一覧、勤怠詳細
    Route::get('/attendance/list', [ListController::class,'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id?}', [ListController::class,'detail'])->name('attendance.detail');
    // 勤怠・休憩修正申請
    Route::post('/correction/request/{id?}',[CorrectionController::class,'correctionRequest'])->name('correction.request');
    Route::post('/create/request',[CorrectionController::class,'createRequest'])->name('create.request');
    // 申請一覧、申請詳細
    Route::get('/stamp_correction_request/list',[CorrectionController::class,'correctionRequestList'])->name('correction.request.list');
    Route::get('/stamp_correction_request/detail/{requestBatchId?}', [CorrectionController::class, 'correctionRequestDetail'])->name('correction.request.detail');
});