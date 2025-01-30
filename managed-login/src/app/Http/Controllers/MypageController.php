<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\CognitoService;

/**
 * マイページを表示するコントローラー
 */
class MypageController extends Controller
{
    /**
     * マイページを表示する indexページ
     * /mypage
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('mypage');
    }
}