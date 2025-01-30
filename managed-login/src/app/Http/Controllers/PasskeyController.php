<?php

namespace App\Http\Controllers;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\CognitoService;

/**
 * パスキー管理を行うコントローラー
 */
class PasskeyController extends Controller
{
    protected $cognito;
    protected $cognitoService;

    public function __construct(CognitoService $cognito)
    {
        $this->cognitoService = $cognito;
        $this->cognito = new CognitoIdentityProviderClient([
            'region'  => 'ap-northeast-1',
            'version' => 'latest'
        ]);
    }

    /**
     * パスキー(ListWebAuthnCredentials)一覧を取得して表示
     * /passkeys
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $accessToken = session('access_token');
        if (!$accessToken) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        try {
            // WebAuthnパスキー一覧を取得
            $result = $this->cognito->listWebAuthnCredentials([
                'AccessToken' => $accessToken,
            ]);

            // パスキーの配列
            $credentials = $result['Credentials'] ?? [];
            // それぞれに CredentialId, FriendlyName, CreatedDate などが含まれる

            return view('passkey.index', compact('credentials'));

        } catch (AwsException $e) {
            return redirect()->back()->withErrors([
                'error' => $e->getAwsErrorMessage()
            ]);
        }
    }

    /**
     * 指定のPasskeyを削除(DeleteWebAuthnCredential)
     * /passkeys/delete
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        $accessToken = session('access_token');
        if (!$accessToken) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        $credentialId = $request->input('credential_id'); // POSTで受け取る

        try {
            $this->cognito->deleteWebAuthnCredential([
                'AccessToken'  => $accessToken,
                'CredentialId' => $credentialId,
            ]);

            // 成功したら一覧へリダイレクト
            return redirect()->route('passkey.index')
                            ->with('status', 'Passkey has been removed.');

        } catch (AwsException $e) {
            return redirect()->route('passkey.index')
                            ->withErrors(['error' => $e->getAwsErrorMessage()]);
        }
    }

    /**
     * パスキー登録画面へのリダイレクト
     * /passkey
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passkey()
    {
        return redirect($this->cognitoService->getPasskeyUrl());
    }
}
