<?php

namespace App\Http\Controllers;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * MFAの設定状況を表示するコントローラー
 */
class MfaController extends Controller
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
     * MFAの設定状況を表示する indexページ
     * /mfa
     * 
     * @return \Illuminate\View\View
     */
    public function showStatus()
    {
        $accessToken = session('access_token');
        // CognitoのGetUser でユーザ情報を取得する
        $result = $this->cognito->getUser([
            'AccessToken' => $accessToken,
        ]);

        // ユーザ属性を配列として取り出し
        $userAttributes = $result->get('UserAttributes') ?? [];
        $userMfaSettingList = $result->get('UserMFASettingList') ?? [];
        $preferredMfaSetting = $result->get('PreferredMfaSetting');

        // phone_number_verified を探す
        $phoneVerified = false;

        foreach ($userAttributes as $attr) {
            if ($attr['Name'] === 'phone_number_verified' && $attr['Value'] === 'true') {
                $phoneVerified = true;
            }
        }

        // ソフトウェアトークン (Authenticatorアプリ) が有効かどうか
        $hasSoftwareTokenMfa = in_array('SOFTWARE_TOKEN_MFA', $userMfaSettingList);

        // SMS MFAが有効かどうか
        $hasSmsMfa = in_array('SMS_MFA', $userMfaSettingList);

        // 画面に渡す
        return view('mfa.status', [
            'phoneVerified' => $phoneVerified,
            'hasSoftwareTokenMfa' => $hasSoftwareTokenMfa,
            'hasSmsMfa' => $hasSmsMfa,
            'preferredMfaSetting' => $preferredMfaSetting,
        ]);
    }

    /**
     * selectで選択した内容によって、MFAの優先設定を更新する
     * /mfa/preference
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePreference(Request $request)
    {
        $accessToken = session('access_token');
        // 入力検証
        $validated = $request->validate([
            'mfa_preferred' => ['required', 'in:SMS_MFA,SOFTWARE_TOKEN_MFA,NONE'],
        ], [
            'mfa_preferred.required' => 'MFAの優先設定を選択してください。',
            'mfa_preferred.in' => '指定されたMFAの設定が無効です。',
        ]);

        $selectedMfa = $validated['mfa_preferred'];
        
        try {
            if ($selectedMfa === 'SMS_MFA') {
                // SMSを優先MFAに
                $this->cognito->setUserMFAPreference([
                    'AccessToken' => $accessToken,
                    'SMSMfaSettings' => [
                        'Enabled' => true,
                        'PreferredMfa' => true,
                    ]
                ]);
            } elseif ($selectedMfa === 'SOFTWARE_TOKEN_MFA') {
                // Authenticatorアプリを優先MFAに
                $this->cognito->setUserMFAPreference([
                    'AccessToken' => $accessToken,
                    'SoftwareTokenMfaSettings' => [
                        'Enabled' => true,
                        'PreferredMfa' => true,
                    ]
                ]);
            } else {
                // なし (MFAオフ)
                // CognitoでMFAをオフにするには両方オフにするか、Optional設定でUserMFASettingListを空にする
                $this->cognito->setUserMFAPreference([
                    'AccessToken' => $accessToken,
                    'SMSMfaSettings' => [
                        'Enabled' => false,
                        'PreferredMfa' => false,
                    ],
                    'SoftwareTokenMfaSettings' => [
                        'Enabled' => false,
                        'PreferredMfa' => false,
                    ],
                ]);
            }

            return redirect()->route('mfa.showstatus')
                ->with('success', 'MFAの優先設定を更新しました。');
        } catch (\Exception $e) {
            return redirect()->route('mfa.showstatus')
                ->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }
}
