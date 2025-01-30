import React, { useEffect, useState } from 'react';
import {
  associateWebAuthnCredential,
  listWebAuthnCredentials,
  deleteWebAuthnCredential
} from 'aws-amplify/auth';

export const SetupPasskey: React.FC = () => {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [passkeys, setPasskeys] = useState<{ id: string; name: string }[]>([]);
  const [registering, setRegistering] = useState(false);

  // Passkey 設定状況を取得
  useEffect(() => {
    const fetchPasskeyStatus = async () => {
      setLoading(true);
      setError('');
      try {

        // Passkey の一覧を取得
        const { credentials } = await listWebAuthnCredentials(); // 修正: credentials を取得
        setPasskeys(
          credentials
            .filter(cred => cred.credentialId !== undefined) // `undefined` をフィルタリング
            .map(cred => ({
              id: cred.credentialId as string, // 型を確定
              name: cred.friendlyCredentialName || 'Passkey'
            }))
        );
      } catch (err: any) {
        setError(err?.message || 'Failed to fetch WebAuthn settings');
      } finally {
        setLoading(false);
      }
    };
    fetchPasskeyStatus();
  }, []);

  // Passkey を登録する
  const handleRegisterPasskey = async () => {
    try {
      setError('');
      setRegistering(true);
      await associateWebAuthnCredential();
      window.location.reload(); // 登録後にリロード
    } catch (err: any) {
      setError(err?.message || 'Passkey registration failed');
    } finally {
      setRegistering(false);
    }
  };

  // Passkey を削除する
  const handleDeletePasskey = async (credentialId: string) => {
    try {
      setError('');
      await deleteWebAuthnCredential({ credentialId });
      setPasskeys(passkeys.filter(pk => pk.id !== credentialId));
    } catch (err: any) {
      setError(err?.message || 'Failed to delete Passkey');
    }
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  return (
    <div style={{ maxWidth: 500, margin: '0 auto' }}>
      <h2>Passkey (WebAuthn) 設定</h2>
      {error && <div style={{ color: 'red' }}>{error}</div>}

      {passkeys.length > 0 ? (
        <div>
          <h3>登録済みの Passkey</h3>
          <ul>
            {passkeys.map(pk => (
              <li key={pk.id}>
                {pk.name}
                <button onClick={() => handleDeletePasskey(pk.id)} style={{ marginLeft: 10 }}>削除</button>
              </li>
            ))}
          </ul>
        </div>
      ) : (
        <p>まだ Passkey は登録されていません。</p>
      )}

      <button onClick={handleRegisterPasskey} disabled={registering}>
        {registering ? '登録中...' : 'Passkey を登録'}
      </button>
    </div>
  );
};
