import React, { useState } from 'react';
import { updatePassword } from 'aws-amplify/auth';

export const ChangePassword: React.FC = () => {
  const [oldPassword, setOldPassword] = useState(''); // 現在のパスワード
  const [newPassword, setNewPassword] = useState(''); // 新しいパスワード
  const [confirmPassword, setConfirmPassword] = useState(''); // 新しいパスワード（確認）

  const [error, setError] = useState(''); // エラーメッセージ
  const [success, setSuccess] = useState(''); // 成功メッセージ

  /**
   * 変更ボタンが押された時の処理
   * @param e 
   */
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    // 新しいパスワードと確認が一致しているかチェック
    if (newPassword !== confirmPassword) {
      setError('新しいパスワードが一致しません。');
      return;
    }

    try {
      // Amplifyの updatePassword を呼び出す
      await updatePassword({
        oldPassword,  // 現在のパスワード
        newPassword,      // 新しいパスワード
      });

      // 成功時のメッセージ
      setSuccess('パスワードを変更しました。');
      // 入力をクリア
      setOldPassword('');
      setNewPassword('');
      setConfirmPassword('');
    } catch (err: any) {
      // エラーメッセージを表示
      setError(err?.message || 'パスワード変更中にエラーが発生しました。');
    }
  };

  return (
    <div style={{ maxWidth: 400, margin: '0 auto' }}>
      <h2>パスワード変更</h2>
      {error && <div style={{ color: 'red', marginBottom: 10 }}>{error}</div>}
      {success && <div style={{ color: 'green', marginBottom: 10 }}>{success}</div>}

      <form onSubmit={handleSubmit}>
        <div style={{ marginBottom: 10 }}>
          <label>現在のパスワード</label>
          <input
            type="password"
            value={oldPassword}
            onChange={(e) => setOldPassword(e.target.value)}
            required
          />
        </div>
        <div style={{ marginBottom: 10 }}>
          <label>新しいパスワード</label>
          <input
            type="password"
            value={newPassword}
            onChange={(e) => setNewPassword(e.target.value)}
            required
          />
        </div>
        <div style={{ marginBottom: 10 }}>
          <label>新しいパスワード（確認）</label>
          <input
            type="password"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            required
          />
        </div>

        <button type="submit">変更</button>
      </form>
    </div>
  );
};
