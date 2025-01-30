<?php

namespace App\Http\Controllers;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * TOTPの設定を行うコントローラー
 */
class TotpController extends Controller
{
    protected $cognito;

    public function __construct()
    {
        $this->cognito = new CognitoIdentityProviderClient([
            'region'  => 'ap-northeast-1',
            'version' => 'latest'
        ]);
    }

    /**
     * TOTPの設定画面を表示する
     *  画面表示と同時にQRコードを表示している
     *  そのため、認証コードエラーなどの時は、Sessionに格納した情報からQRコードを再作成する
     *  理由は、再描画時にQRコードが変わってしまうと、アプリに再登録が必要なため.
     *  コード検証後にSessionから削除する
     * /mfa/totp/setup
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function setup(Request $request)
    {
        $accessToken = session('access_token');
        $secretCode = session('totp_secret_code');

        if (!$secretCode) {
            // CognitoにAssociateSoftwareTokenをリクエスト
            $result = $this->cognito->associateSoftwareToken([
                'AccessToken' => $accessToken,
            ]);

            $secretCode = $result->get('SecretCode');
        }

        // otpauth URL生成
        $issuer    = urlencode(env('APP_NAME'));
        $userName  = urlencode(Auth::user()->email);
        $otpUrl    = "otpauth://totp/{$issuer}:{$userName}?secret={$secretCode}&issuer={$issuer}";

        // QRコードを生成
        $qrCode = new QrCode(
            $otpUrl);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $qrCodeBase64 = base64_encode($result->getString());
        // セッションに格納
        session([
            'totp_secret_code' => $secretCode,
        ]);

        return view('mfa.totp.setup', [
            'qrCodeBase64' => $qrCodeBase64,
            'secretCode'   => $secretCode,
        ]);
    }

    /**
     * TOTPの有効化する
     * /mfa/totp/verify
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyAndEnable(Request $request)
    {
        $accessToken = session('access_token');
        // 入力検証
        $validated = $request->validate([
            'totp_code' => ['required', 'digits:6'], // 6桁の数字であることを要求
        ], [
            'totp_code.required' => 'TOTPコードを入力してください。',
            'totp_code.digits' => 'TOTPコードは6桁の数字で入力してください。',
        ]);

        $code = $validated['totp_code'];
        try {
            // VerifySoftwareToken
            $verifyResult = $this->cognito->verifySoftwareToken([
                'UserCode' => $code,
                'AccessToken' => $accessToken,
            ]);

            $status = $verifyResult->get('Status'); // SUCCESS or ERROR

            if ($status !== 'SUCCESS') {
                return redirect()->back()->withErrors(['totp_code' => 'Invalid code. Please try again.']);
            }

            // TOTPをPreferredなMFAに設定
            $this->cognito->setUserMFAPreference([
                'AccessToken' => $accessToken,
                'SoftwareTokenMfaSettings' => [
                    'Enabled' => true,
                    'PreferredMfa' => true,
                ],
            ]);

            // 成功後、セッションデータを削除
            session()->forget(['totp_secret_code']);
            return redirect()->route('mfa.showstatus')->with('status', 'TOTPを有効化しました。');
        } catch (\Aws\Exception\AwsException $e) {
            return redirect()->back()->withErrors(['totp_code' => 'コードが検証できませんでした']);
        }
    }

    /**
     * TOTP(Authenticatorアプリ)を無効化する
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(Request $request)
    {
        $accessToken = session('access_token');
        try {
            // SoftwareTokenMfaSettings を false にする
            $this->cognito->setUserMFAPreference([
                'AccessToken' => $accessToken,
                'SoftwareTokenMfaSettings' => [
                    'Enabled' => false,
                    'PreferredMfa' => false,
                ],
            ]);

            return redirect()->route('mfa.showstatus')->with('success', 'TOTPを無効化しました。');
        } catch (\Exception $e) {
            return redirect()->route('mfa.showstatus')->with('error', 'エラーが発生しました: '.$e->getMessage());
        }
    }

}