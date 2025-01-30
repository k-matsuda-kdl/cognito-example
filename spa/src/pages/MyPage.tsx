import React from 'react';
import { getCurrentUser, signOut } from 'aws-amplify/auth';

export const MyPage: React.FC = () => {
  const [user, setUser] = React.useState<any>(null); // ユーザー情報
  const [loading, setLoading] = React.useState(true); // ローディング中か
  const [error, setError] = React.useState<string | null>(null); // エラーメッセージ

  // 初期化: ログイン中ユーザーを取得
  React.useEffect(() => {
    // コンポーネントマウント時にログイン中ユーザーを取得
    getCurrentUser()
      .then((currentUser) => {
        setUser(currentUser);
        setLoading(false);
      })
      .catch((err) => {
        console.error(err);
        setError('Not logged in');
        setLoading(false);
      });
  }, []);

  /**
   * サインアウトボタンが押された時の処理
   */
  const handleSignOut = async () => {
    try {
      // Amplifyのトークン破棄
      await signOut();
      // ログアウト後にトップページ or ログインページへ遷移
      window.location.href = '/';
    } catch (err) {
      console.error(err);
      setError('Error signing out');
    }
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  if (error || !user) {
    return <div>Error or not logged in: {error}</div>;
  }

  return (
    <div>
      <h1>My Page</h1>
      <p><a href="/changepassword">パスワード変更</a></p>
      <p><a href="/mfasettings">MFA設定</a></p>
      <button onClick={handleSignOut}>Sign Out</button>
    </div>
  );
};
