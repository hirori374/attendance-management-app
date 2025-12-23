<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ListController;
use App\Http\Controllers\Admin\CorrectionController;
use App\Http\Controllers\Admin\StaffController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/
Route::get('/login', [AuthController::class, 'loginView'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::guard('admin')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/admin/login');
})->name('admin.logout');

Route::middleware(['auth:admin'])->group(function () {
    // 勤怠一覧・詳細表示
    Route::get('/attendance/list', [ListController::class, 'attendanceList'])->name('admin.attendance.list');
    Route::get('/attendance/{id?}', [ListController::class, 'detail'])->name('admin.attendance.detail');

    // 勤怠修正、勤怠新規作成
    Route::patch('/correction/{id}', [CorrectionController::class, 'correction'])->name('admin.correction');
    Route::post('/attendance/create', [CorrectionController::class, 'attendanceCreate'])->name('admin.attendance.create');
    // 修正申請一覧、詳細、承認
    Route::get('/stamp_correction_request/list',[CorrectionController::class,'correctionRequestList'])->name('admin.correction.request.list');
    Route::get('/stamp_correction_request/approve/{requestBatchId}', [CorrectionController::class, 'requestDetail'])->name('admin.request.detail');
    Route::post('/stamp_correction_request/approve/{requestBatchId}', [CorrectionController::class, 'approve'])->name('admin.approve');

    // スタッフ一覧、月次勤務、CSV出力
    Route::get('/staff/list', [StaffController::class, 'staffList'])->name('admin.staff.list');
    Route::get('/attendance/staff/{userId}', [StaffController::class, 'attendanceStaff'])->name('admin.attendance.staff');
    Route::get('/attendance/{userId}/export', [StaffController::class, 'export'])->name('admin.attendance.export');
});