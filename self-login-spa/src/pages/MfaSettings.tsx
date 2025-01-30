import React, { useEffect, useState } from 'react';
import {
  fetchMFAPreference,
  updateMFAPreference,
} from 'aws-amplify/auth';

type MfaStatus = 'NONE' | 'SMS' | 'TOTP' | 'EMAIL';

export const MfaSettings: React.FC = () => {
  const [preferredMfa, setPreferredMfa] = useState<MfaStatus>('NONE'); // 現在の優先設定
  const [selectedMfa, setSelectedMfa] = useState<MfaStatus>('NONE'); // 選択中のMFA
  const [hasSmsMfa, setHasSmsMfa] = useState(false); // SMS MFAが有効か
  const [hasTotpMfa, setHasTotpMfa] = useState(false); // TOTP MFAが有効か
  const [loading, setLoading] = useState(true); // ローディング中か
  const [error, setError] = useState(''); // エラーメッセージ
  const [success, setSuccess] = useState(''); // 成功メッセージ

  // 初期化: 現在のMFA設定状況を取得
  useEffect(() => {
    const fetchMfaStatus = async () => {
      setLoading(true);
      setError('');
      try {
        const { enabled, preferred } = await fetchMFAPreference();

        // 現在の状態を設定
        setHasSmsMfa(enabled?.includes('SMS') || false);
        setHasTotpMfa(enabled?.includes('TOTP') || false);
        setPreferredMfa(preferred || 'NONE');
        setSelectedMfa(preferred || 'NONE'); // 初期値を現在の優先設定に
      } catch (err: any) {
        setError(err.message || 'エラーが発生しました');
      } finally {
        setLoading(false);
      }
    };

    fetchMfaStatus();
  }, []);

  /**
   * 更新ボタンが押された時の処理
   * MFAの優先設定を更新
   */
  const handleUpdateMfaPreference = async () => {
    setError('');
    setSuccess('');
    try {
      if (selectedMfa === 'SMS') {
        await updateMFAPreference({ sms: 'PREFERRED' });
      } else if (selectedMfa === 'TOTP') {
        await updateMFAPreference({ totp: 'PREFERRED'});
      } else if (selectedMfa === 'NONE') {
        await updateMFAPreference({
          sms: 'DISABLED',
          totp: 'DISABLED'
        });
      }

      // 成功時に状態を更新
      setPreferredMfa(selectedMfa);
      setSuccess('MFAの優先設定を更新しました。');
    } catch (err: any) {
      setError(err.message || 'MFAの更新に失敗しました');
    }
  };
  
  /**
   * SMSのMFA設定を無効にする
   * @returns 
   */
  const disableSms = async () => {
    setError('');
    setSuccess('');
    // 確認ポップアップ
    const confirm = window.confirm('SMSを無効にしますか？');
    if (!confirm) {
      return; // ユーザーがキャンセルした場合は処理を中断
    }
    try {
      await updateMFAPreference({ sms: 'DISABLED' });
      const { enabled, preferred } = await fetchMFAPreference();

      // 現在の状態を設定
      setHasSmsMfa(enabled?.includes('SMS') || false);
      setHasTotpMfa(enabled?.includes('TOTP') || false);
      setPreferredMfa(preferred || 'NONE');
      setSelectedMfa(preferred || 'NONE'); // 初期値を現在の優先設定に
      setSuccess('SMSを無効にしました');
    } catch (err: any) {
      setError(err.message || 'SMSの更新に失敗しました');
    }
  };
  
  /**
   * AuthenticatorアプリのMFA設定を無効にする
   */
  const disableTotp = async () => {
    setError('');
    setSuccess('');
    // 確認ポップアップ
    const confirm = window.confirm('Authenticatorアプリを無効にしますか？');
    if (!confirm) {
      return; // ユーザーがキャンセルした場合は処理を中断
    }
    try {
      await updateMFAPreference({ totp: 'DISABLED' });
      const { enabled, preferred } = await fetchMFAPreference();

      // 現在の状態を設定
      setHasSmsMfa(enabled?.includes('SMS') || false);
      setHasTotpMfa(enabled?.includes('TOTP') || false);
      setPreferredMfa(preferred || 'NONE');
      setSelectedMfa(preferred || 'NONE'); // 初期値を現在の優先設定に

      setSuccess('Authenticatorアプリを無効にしました');
    } catch (err: any) {
      setError(err.message || 'Authenticatorアプリの更新に失敗しました');
    }
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  return (
    <div style={{ maxWidth: '600px', margin: '0 auto' }}>
      <h1>MFAステータス確認</h1>

      {error && <div style={{ color: 'red' }}>{error}</div>}
      {success && <div style={{ color: 'green' }}>{success}</div>}

      <p>現在の優先MFA: 
        {preferredMfa === 'SMS'
          ? 'SMSメッセージ'
          : preferredMfa === 'TOTP'
          ? 'Authenticatorアプリ'
          : 'なし'}
      </p>

      <p>優先MFAを変更:</p>
      <div>
        <select
          value={selectedMfa}
          onChange={(e) => setSelectedMfa(e.target.value as MfaStatus)}
        >
          {hasSmsMfa && <option value="SMS">SMSメッセージ</option>}
          {hasTotpMfa && <option value="TOTP">Authenticatorアプリ</option>}
          <option value="NONE">なし</option>
        </select>
        <button
          onClick={handleUpdateMfaPreference}
          style={{ marginLeft: '10px' }}
        >
          更新
        </button>
      </div>

      <h2>MFAの状態</h2>
      <p>
        Authenticatorアプリ (TOTP):{' '}
        {hasTotpMfa ? (
          <span style={{ color: 'green' }}>
            有効
            <a onClick={disableTotp} style={{ marginLeft: '10px' }}>
              無効にする
            </a>
          </span>
        ) : (
          <span style={{ color: 'red' }}>
            無効
            <a href='/setuptotp' style={{ marginLeft: '10px' }}>
              設定
            </a>
          </span>
        )}
      </p>
      <p>
        SMSメッセージ:{' '}
        {hasSmsMfa ? (
          <span style={{ color: 'green' }}>
            有効
            <a onClick={disableSms} style={{ marginLeft: '10px' }}>
              無効にする
            </a>
          </span>
        ) : (
          <span style={{ color: 'red' }}>
            無効
            <a href='/setupsms' style={{ marginLeft: '10px' }}>
              設定
            </a>
          </span>
        )}
      </p>
    </div>
  );
};
