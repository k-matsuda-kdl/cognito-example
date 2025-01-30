<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

/**
 * パスワード変更を行うコントローラー
 */
class PasswordController extends Controller
{    
    private $cognitoClient;

    public function __construct()
    {
        $this->cognitoClient = new CognitoIdentityProviderClient([
            'region'  => 'ap-northeast-1',
            'version' => 'latest'
        ]);
    }
    /**
     * パスワード変更画面を表示
     * /password-change
     * 
     * @return \Illuminate\View\View
     */
    public function showChangePasswordForm()
    {
        return view('password-change');
    }

    /**
     * パスワード変更処理
     * /password-change
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $accessToken = session('access_token');
        if (!$accessToken) {
            return redirect('/')->with('error', 'ログイン情報がありません。再度ログインしてください。');
        }

        try {
            // ChangePassword API 呼び出し
            $result = $this->cognitoClient->changePassword([
                'AccessToken'      => $accessToken,
                'PreviousPassword' => $request->input('current_password'),
                'ProposedPassword' => $request->input('new_password'),
            ]);

            return redirect()->route('mypage')->with('success', 'パスワードが正常に変更されました。');

        } catch (\Aws\Exception\AwsException $e) {
            // エラーをログに記録
            logger()->error('パスワード変更エラー', [
                'message' => $e->getMessage(),
                'aws_error' => $e->getAwsErrorMessage(),
            ]);

            return back()->with('error', 'パスワード変更に失敗しました。もう一度お試しください。');
        }
    }
}
