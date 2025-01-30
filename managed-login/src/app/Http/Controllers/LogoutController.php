<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Services\CognitoService;

/**
 * ログアウト処理を行うコントローラー
 */
class LogoutController extends Controller
{    
    protected $cognito;
    public function __construct(CognitoService $cognito)
    {
        $this->cognito = $cognito;
    }
    /**
     * ログアウト処理 ログアウト後Cognitoのセッションも無効化する
     * /logout
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    
        // Cognitoにもログアウトさせるため、Cognito用ログアウトURLへリダイレクト
        return redirect($this->cognito->getLogoutUrl());
    }
}
