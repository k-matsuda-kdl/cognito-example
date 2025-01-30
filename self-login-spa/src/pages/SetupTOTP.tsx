import React, { useEffect, useState } from 'react';
import { fetchMFAPreference, setUpTOTP, verifyTOTPSetup, updateMFAPreference, fetchUserAttributes } from 'aws-amplify/auth';
import { QRCodeSVG  } from 'qrcode.react'; // QRコード表示用ライブラリ

/**
 * TOTPのotpauth URLを生成
 * @param issuer 
 * @param username 
 * @param secret 
 * @returns 
 */
function buildOtpauthUrl(issuer: string, username: string, secret: string) {
  // 例: otpauth://totp/<issuer>:<username>?secret=<secret>&issuer=<issuer>
  const encodedIssuer = encodeURIComponent(issuer);
  const encodedUser = encodeURIComponent(username);
  return `otpauth://totp/${encodedIssuer}:${encodedUser}?secret=${secret}&issuer=${encodedIssuer}`;
}

/**
 * TOTPのQRコードを表示するコンポーネント
 */
export const SetupTOTP: React.FC = () => {
  const [loading, setLoading] = useState(true); // ローディング中か
  const [mfaEnabled, setMfaEnabled] = useState(false);  // 現在TOTPが有効かどうか
  const [secretCode, setSecretCode] = useState('');   // setUpTOTPで返ってくるシークレット
  const [otpCode, setOtpCode] = useState('');         // ユーザーが入力する6桁コード
  const [qrUrl, setQrUrl] = useState(''); // QRコードのURL
  const [step, setStep] = useState<'INIT' | 'SHOW_QR' | 'VERIFYING' | 'DONE'>('INIT'); // ステップ管理
  const [error, setError] = useState(''); // エラーメッセージ

  // 初回マウント時に現在のMFA設定状況を取得
  useEffect(() => {
    const fetchStatus = async () => {
      setLoading(true);
      setError('');
      try {
        const { enabled } = await fetchMFAPreference();
        // totpMfa === 'ENABLED' or 'DISABLED' or undefined
        if (enabled?.includes('TOTP')) {
          setMfaEnabled(true);
          setStep('DONE'); // 既に有効なので何もしない
        } else {
          setMfaEnabled(false);
          setStep('INIT');
        }
      } catch (err: any) {
        setError(err?.message || 'Failed to fetch MFA preference');
      } finally {
        setLoading(false);
      }
    };
    fetchStatus();
  }, []);

  /**
   * TOTPの設定を開始するボタンが押された時の処理
   */
  const handleSetUpTOTP = async () => {
    try {
      setError('');
      // setUpTOTP() でCognitoからTOTPシークレットを取得
      const {sharedSecret} = await setUpTOTP();
      setSecretCode(sharedSecret);

      // ユーザー名・アプリ名などは適宜取得
      const issuer = 'ReactExample';
      const currentUser = await fetchUserAttributes();
      const username = currentUser?.email ?? 'username'; // 本来はログイン中ユーザーから取得

      // otpauth URL生成
      const url = buildOtpauthUrl(issuer, username, sharedSecret);
      setQrUrl(url);

      // ステップを切り替え
      setStep('SHOW_QR');
    } catch (err: any) {
      setError(err?.message || 'Failed to setup TOTP');
    }
  };

  /**
   * 6桁コードを検証するボタンが押された時の処理
   */
  const handleVerify = async () => {
    try {
      setError('');
      setStep('VERIFYING');

      // 6桁コードを検証
      await verifyTOTPSetup({ code: otpCode });

      // 検証成功後、TOTPを有効化 & Preferredに
      await updateMFAPreference({
        totp: 'PREFERRED'
      });

      setStep('DONE');
    } catch (err: any) {
      setError(err?.message || 'Verification failed');
      setStep('SHOW_QR');
    }
  };
  if (loading) {
    return <div>Loading...</div>;
  }
  return (
    <div style={{ maxWidth: 500, margin: '0 auto' }}>
      <h2>MFA (Authenticator App) Setup</h2>
      {error && <div style={{ color: 'red' }}>{error}</div>}

      {step === 'INIT' && !mfaEnabled && (
        <div>
          <p>まだMFAが設定されていません。</p>
          <button onClick={handleSetUpTOTP}>Enable TOTP MFA</button>
        </div>
      )}

      {step === 'SHOW_QR' && (
        <div>
          <p>以下のQRコードをAuthenticatorアプリでスキャンしてください。</p>
          {qrUrl && <QRCodeSVG value={qrUrl} size={200} />}
          <p>あるいはシークレットキー: <b>{secretCode}</b> を手動入力してください。</p>

          <p>アプリに追加できたら、表示される6桁コードを入力:</p>
          <input
            type="text"
            value={otpCode}
            onChange={(e) => setOtpCode(e.target.value)}
            placeholder="123456"
            maxLength={6}
          />
          <button onClick={handleVerify}>Verify</button>
        </div>
      )}

      {step === 'VERIFYING' && (
        <div>Verifying your TOTP code...</div>
      )}

      {step === 'DONE' && (
        <div>
          <p>MFAを有効化です。</p>
        </div>
      )}
    </div>
  );
};
