<?php

namespace App\Http\Controllers;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * SMS MFAの設定を行うコントローラー
 */
class SmsController extends Controller
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
     * SMS MFAの設定状況を表示する indexページ
     * 設定状況に応じて、画面の内容が切り替わる
     * 　電話番号が未検証の場合は、電話番号設定画面を表示
     *   SMS MFAが無効の場合は、SMS MFA有効化画面を表示
     * 
     * /mfa/sms/phone
     * 
     * @return \Illuminate\View\View
     */
    public function showPhoneForm()
    {
        $accessToken = session('access_token');
        if (!$accessToken) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        // GetUser でユーザー属性を取得
        $result = $this->cognito->getUser([
            'AccessToken' => $accessToken,
        ]);

        $userAttributes = $result->get('UserAttributes') ?? [];
        $userMfaSettingList = $result->get('UserMFASettingList') ?? [];
        $preferredMfaSetting = $result->get('PreferredMfaSetting');

        // phone_number および phone_number_verified をチェック
        $phoneNumber = '';
        $phoneVerified = false;
        foreach ($userAttributes as $attr) {
            if ($attr['Name'] === 'phone_number') {
                $phoneNumber = $attr['Value'];
            }
            if ($attr['Name'] === 'phone_number_verified' && $attr['Value'] === 'true') {
                $phoneVerified = true;
            }
        }

        // SMS MFAが有効かどうか
        $hasSmsMfa = in_array('SMS_MFA', $userMfaSettingList);

        // すでに電話番号が検証済み -> SMS MFAをオン/オフする画面を表示
        return view('mfa.sms.setting', [
            'phoneNumber' => $phoneNumber,
            'hasSmsMfa' => $hasSmsMfa,
            'preferredMfaSetting' => $preferredMfaSetting,
            'phoneVerified' => $phoneVerified,
        ]);
    }

    /**
     * 入力された電話番号をCognitoに更新する
     * また、認証コードを送信する
     * /mfa/sms/phone
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePhoneNumber(Request $request)
    {
        $accessToken = session('access_token'); // ログイン後に保持している想定
        // 入力検証
        $validated = $request->validate([
            'phone_number' => [
                'required',
                'regex:/^\+[1-9]\d{1,14}$/', // E.164形式
            ],
        ], [
            'phone_number.required' => '電話番号を入力してください。',
            'phone_number.regex' => '電話番号はE.164形式で入力してください。（例: +818012345678）',
        ]);

        $phone = $validated['phone_number'];// 例: +81 80-xxxx-xxxx (E.164形式に正規化)

        // Cognitoでユーザー属性を更新（電話番号をセット）
        $this->cognito->updateUserAttributes([
            'AccessToken' => $accessToken,
            'UserAttributes' => [
                [
                    'Name'  => 'phone_number',
                    'Value' => $phone,
                ],
            ],
        ]);

        // 認証コードを送る
        $this->cognito->getUserAttributeVerificationCode([
            'AccessToken' => $accessToken,
            'AttributeName' => 'phone_number',
        ]);

        // 次のステップ（認証コード入力画面）へ誘導
        return redirect()->route('mfa.sms.verify_form')->with('phone_number', $phone);
    }

    /**
     * 認証コード入力画面を表示
     * /mfa/sms/verify
     * 
     * @return \Illuminate\View\View
     */
    public function showVerifyForm()
    {
        // 直前の画面から来た時点でセッションに電話番号を入れている場合などを取り出す
        $phone = session('phone_number');
        return view('mfa.sms.verify_form', compact('phone'));
    }

    /**
     * 認証コードを検証する
     * /mfa/sms/verify
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyPhoneNumber(Request $request)
    {
        $accessToken = session('access_token');
        $validated = $request->validate([
            'verification_code' => ['required', 'digits:6'], // 6桁の数字であることを要求
        ], [
            'verification_code.required' => 'コードを入力してください。',
            'verification_code.digits' => 'コードは6桁の数字で入力してください。',
        ]);
        $verificationCode = $validated['verification_code'];
        try {
            // VerifyUserAttribute API を呼び出す
            // (UpdateUserAttributesで自動送信されたSMSコードを検証する)
            $this->cognito->verifyUserAttribute([
                'AccessToken'   => $accessToken,
                'AttributeName' => 'phone_number',
                'Code'          => $verificationCode,
            ]);

            // ここで電話番号が verified (phone_number_verified = true) となる
            // 次は SetUserMFAPreference でSMSを有効化する画面へ誘導
            return redirect()->route('mfa.sms.enable_form');
        } catch (\Aws\Exception\AwsException $e) {
            return redirect()->back()->withErrors(['verification_code' => 'コードが検証できませんでした']);
        }
    }

    /**
     * SMS MFAを有効化する画面を表示
     * /mfa/sms/enable
     * 
     * @return \Illuminate\View\View
     */
    public function showEnableForm()
    {
        return view('mfa.sms.enable_form');
    }
    
    /**
     * SMSMFAを有効化する
     * /mfa/sms/enable
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enable(Request $request)
    {
        $accessToken = session('access_token');
    
        // "SMS_MFA" を有効にする
        $this->cognito->setUserMFAPreference([
            'AccessToken' => $accessToken,
            'SMSMfaSettings' => [
                'Enabled' => true,
                'PreferredMfa' => true,
            ],
        ]);
    
        // これで次回以降ログイン時にSMSコードが要求される
        return redirect()->route('mfa.showstatus')->with('status', 'SMS MFA has been enabled!');
    }
    /**
     * SMSMFAを無効化する
     * /mfa/sms/disable
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
                'SMSMfaSettings' => [
                    'Enabled' => false,
                    'PreferredMfa' => false,
                ],
            ]);

            return redirect()->route('mfa.showstatus')->with('success', 'SMSのMFAを無効化しました。');
        } catch (\Exception $e) {
            return redirect()->route('mfa.showstatus')->with('error', 'エラーが発生しました: '.$e->getMessage());
        }
    }

}