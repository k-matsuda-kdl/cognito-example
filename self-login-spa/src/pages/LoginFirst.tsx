import React, { useState } from "react";
import { handleSignInFlow } from "../services/authService";

type Props = {
  onNextStep: (username: string, nextChallenge: string) => void;
};

/**
 * ログインの最初のステップ (メールアドレスまたはUsernameの入力)
 * @param onNextStep コールバック関数
 * @returns 
 */
export const LoginFirst: React.FC<Props> = ({ onNextStep }) => {
  const [username, setUsername] = useState("");
  const [error, setError] = useState("");

  /**
   * 次へボタンが押された時の処理
   * handleSignInFlowにて、Cognitoからの応答内容により次のステップに進む
   * 
   * @param event onNextStepは、親コンポーネントのhandleNextStep関数
   */
  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError("");

    try {
      await handleSignInFlow(username, null, onNextStep);
    } catch (err: any) {
      setError(err.message);
    }
  };

  return (
    <div>
      <h2>ログイン</h2>
      <form onSubmit={handleSubmit}>
        <label>メールアドレスまたはUsername:</label>
        <input
          type="text"
          value={username}
          onChange={(e) => setUsername(e.target.value)}
          required
        />
        <button type="submit">次へ</button>
      </form>
      {error && <p style={{ color: "red" }}>{error}</p>}
    </div>
  );
};
