<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\CognitoService;

class CallbackController extends Controller
{
    protected $cognito;
    public function __construct(CognitoService $cognito)
    {
        $this->cognito = $cognito;
    }

    /**
     * Cognitoのログインからのコールバック処理
     * ログイン成功時はユーザーを作成または更新し、
     * ログイン処理を行ったうえで、Sessionにトークンを保存してマイページにリダイレクト
     * 
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        $code = $request->query('code');
        logger()->error($code);
        if (!$code) {
            return redirect('/')->with('error', 'ログイン失敗');
        }
    
        // IDトークン取得リクエスト
        $response = Http::asForm()->post($this->cognito->getTokenEndpoint(), [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('COGNITO_CLIENT_ID'),
            'client_secret' => env('COGNITO_CLIENT_SECRET'),
            'code'          => $code,
            'redirect_uri'  => env('COGNITO_REDIRECT_URI'),
        ]);

        if ($response->failed()) {
            logger()->error([$response->body(), $response->status()]);
            return redirect('/')->with('error', 'トークン取得失敗');
        }
    
        $tokens = $response->json();

        $idToken = $tokens['id_token'];
        $accessToken = $tokens['access_token'];
        $refreshToken = $tokens['refresh_token'];
        
        // IDトークンをデコード
        // ただし、このケースではid_tokenの検証は省略しています. また使っていません.
        $decodedToken = json_decode(base64_decode(explode('.', $idToken)[1]), true);
    
        // ユーザーの作成または更新
        $user = User::updateOrCreate(
            ['sub' => $decodedToken['sub']], // 一意なユーザーID (Cognitoの "sub" クレーム)
            [
                'email' => $decodedToken['email'],
                'name'  => $decodedToken['name'] ?? 'ゲスト',
            ]
        );
    
        // ログイン処理
        Auth::login($user);
        // トークンをセッションに保存
        session([
            'refresh_token' => $refreshToken,
            'access_token' => $accessToken,
            'id_token'     => $idToken,
        ]);
        return redirect()->route('mypage');
    }

}
