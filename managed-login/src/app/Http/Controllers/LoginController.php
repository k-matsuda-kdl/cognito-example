<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Services\CognitoService;

/**
 * ログイン処理を行うコントローラー
 */
class LoginController extends Controller
{    
    protected $cognito;

    public function __construct(CognitoService $cognito)
    {
        $this->cognito = $cognito;
    }

    /**
     * ログイン画面ですが、Cognitoにリダイレクトさせるだけです
     * /login
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login()
    {
        return redirect($this->cognito->getLoginUrl());
    }
}
