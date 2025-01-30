<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MfaController;
use App\Http\Controllers\PasskeyController;
use App\Http\Controllers\TotpController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MypageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/callback', [CallbackController::class, 'callback']);

Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// 認証済みユーザー専用ルート
Route::middleware(['auth'])->group(function () {
    // マイページ
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage');
    // パスワード変更関連ページ
    Route::get('/password-change', [PasswordController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password-change', [PasswordController::class, 'changePassword'])->name('password.change.post');
    // MFA関連ページ
    Route::get('/mfa', [MfaController::class, 'showStatus'])->name('mfa.showstatus');
    Route::get('/mfa/totp/setup', [TotpController::class, 'setup'])->name('mfa.totp.setup');
    Route::post('/mfa/totp/verify', [TotpController::class, 'verifyAndEnable'])->name('mfa.totp.verify');
    Route::get('/mfa/totp/disable', [TotpController::class, 'disable'])->name('mfa.totp.disable');
    Route::get('/mfa/sms/phone', [SmsController::class, 'showPhoneForm'])->name('mfa.sms.phone_form');
    Route::post('/mfa/sms/phone', [SmsController::class, 'updatePhoneNumber'])->name('mfa.sms.phone_update');
    Route::get('/mfa/sms/verify', [SmsController::class, 'showVerifyForm'])->name('mfa.sms.verify_form');
    Route::post('/mfa/sms/verify', [SmsController::class, 'verifyPhoneNumber'])->name('mfa.sms.verify_code');        
    Route::get('/mfa/sms/enable', [SmsController::class, 'showEnableForm'])->name('mfa.sms.enable_form');
    Route::post('/mfa/sms/enable', [SmsController::class, 'enable'])->name('mfa.sms.enable');    
    Route::get('/mfa/sms/disable', [SmsController::class, 'disable'])->name('mfa.sms.disable');    
    Route::post('/mfa/preference', [MfaController::class, 'updatePreference'])->name('mfa.updatePreference');

    // Passkey マネージドログインに遷移
    Route::get('/passkey', [PasskeyController::class, 'passkey'])->name('passkey');
    // パスキー一覧表示
    Route::get('/passkeys', [PasskeyController::class, 'index'])->name('passkey.index');
    // パスキー削除
    Route::post('/passkeys/delete', [PasskeyController::class, 'delete'])->name('passkey.delete');
});