import React, { useEffect, useState } from 'react';
import {
  fetchUserAttributes,
  updateUserAttribute,
  sendUserAttributeVerificationCode,
  confirmUserAttribute,
  updateMFAPreference
} from 'aws-amplify/auth';

// SMS MFAの設定のStepの定義
type Step =
  | 'LOADING'           // 初期ロード中
  | 'PHONE_FORM'        // 電話番号入力
  | 'VERIFY_CODE_FORM'  // 検証コード入力
  | 'ENABLE_MFA'        // SMSを有効にする
  | 'DONE';             // 完了 or 既に設定済み

/**
 * SMS MFAの設定コンポーネント
 * @returns 
 */
export const SetupSMS: React.FC = () => {
  const [step, setStep] = useState<Step>('LOADING'); // Stepの状態

  // フォーム入力用
  const [phoneNumber, setPhoneNumber] = useState('');
  const [verifyCode, setVerifyCode] = useState('');

  // 状態管理
  const [phoneVerified, setPhoneVerified] = useState(false);
  const [error, setError] = useState('');
  const [status, setStatus] = useState('');

  // 初期化: 初回マウント時、ユーザー属性を確認
  useEffect(() => {
    const checkPhoneNumber = async () => {
      try {
        setError('');
        setStatus('');
        const attrs = await fetchUserAttributes();
        // attrs は [{ Name:'email',Value:'xxx'}, {Name:'phone_number',Value:'+81xxx'}, {Name:'phone_number_verified',Value:'true'},...]
        const phone = attrs.phone_number || '';
        const phoneVerified = attrs.phone_number_verified === 'true';
        
        setPhoneNumber(phone);
        setPhoneVerified(phoneVerified);

        if (phone && phoneVerified) {
          // 電話番号が既に登録＆検証済み
          setStep('ENABLE_MFA');
        } else {
          // 未登録 or 未検証
          setStep('PHONE_FORM');
        }
      } catch (err: any) {
        setError(err?.message || 'Failed to fetch user attributes.');
      }
    };

    checkPhoneNumber().then(() => {
      // do nothing
    });
  }, []);

  /**
   * 電話番号更新ボタンが押された時の処理
   * 電話番号を更新し、検証コードを送信
   */
  const handleUpdatePhone = async () => {
    setError('');
    setStatus('');

    if (!phoneNumber) {
      setError('電話番号を入力してください');
      return;
    }
    try {
      // E.164形式 e.g. +818012345678
      // もしユーザーが日本語形式で入力した場合は変換が必要
      // ここでは入力済みとしてそのまま使用

      await updateUserAttribute({ userAttribute: {attributeKey: 'phone_number', value: phoneNumber} });

      // 電話番号更新後、SMSで検証コードを送る
      await sendUserAttributeVerificationCode({userAttributeKey: 'phone_number'});
      setStatus('検証コードを送信しました。SMSを確認してください。');
      setStep('VERIFY_CODE_FORM');
    } catch (err: any) {
      setError(err?.message || 'Failed to update phone number or send code.');
    }
  };

  /**
  * コードの検証ボタンが押されたときの処理
  * 検証コードを入力し、電話番号を検証
  */
  const handleVerifyCode = async () => {
    setError('');
    setStatus('');
    if (!verifyCode) {
      setError('検証コードを入力してください');
      return;
    }
    try {
      await confirmUserAttribute({
        userAttributeKey: 'phone_number',
        confirmationCode: verifyCode,
      });

      setPhoneVerified(true);
      setStep('ENABLE_MFA');
      setStatus('電話番号が検証されました。');
    } catch (err: any) {
      setError(err?.message || 'Failed to verify code.');
    }
  };

  /**
   * SMS MFAを有効化ボタンが押された時の処理
   * SMS MFAを有効化する
   */
  const handleEnableMFA = async () => {
    setError('');
    setStatus('');
    try {
      await updateMFAPreference({
        sms: 'PREFERRED'
      });

      setStep('DONE');
      setStatus('SMS MFAが有効化されました。');
    } catch (err: any) {
      setError(err?.message || 'Failed to enable SMS MFA');
    }
  };

  // 画面描画
  return (
    <div style={{ maxWidth: 400, margin: '0 auto' }}>
      <h2>SMS MFA Setup</h2>
      {error && <div style={{ color: 'red', marginBottom: 10 }}>{error}</div>}
      {status && <div style={{ color: 'green', marginBottom: 10 }}>{status}</div>}

      {step === 'LOADING' && (
        <div>Loading user info...</div>
      )}

      {step === 'PHONE_FORM' && (
        <div>
          <p>電話番号を登録してください (E.164形式)。</p>
          <input
            type="text"
            value={phoneNumber}
            onChange={(e) => setPhoneNumber(e.target.value)}
            placeholder="+818012345678"
          />
          <button onClick={handleUpdatePhone}>登録してコードを送信</button>
        </div>
      )}

      {step === 'VERIFY_CODE_FORM' && (
        <div>
          <p>SMSにて送られた検証コードを入力してください。</p>
          <input
            type="text"
            value={verifyCode}
            onChange={(e) => setVerifyCode(e.target.value)}
            placeholder="123456"
            maxLength={6}
          />
          <button onClick={handleVerifyCode}>検証</button>
        </div>
      )}

      {step === 'ENABLE_MFA' && phoneVerified && (
        <div>
          <p>電話番号が検証されました。SMSをMFAとして有効化できます。</p>
          <button onClick={handleEnableMFA}>SMS MFAを有効化</button>
        </div>
      )}

      {step === 'DONE' && (
        <div>
          <p>SMS MFAが有効です。</p>
        </div>
      )}
    </div>
  );
};
