import React, { useState } from "react";
import { signIn } from "aws-amplify/auth";
import { handleSignInFlow } from "../services/authService";

type Props = {
  username: string;
  onNextStep: (username: string, nextChallenge: string) => void;
};

/**
 * SMS 認証コードの入力ステップ
 * @param {username, onNextStep}
 * @returns 
 */
export const LoginStepSMS: React.FC<Props> = ({ username, onNextStep }) => {
  const [smsCode, setSmsCode] = useState("");
  const [error, setError] = useState("");
  const [successMessage, setSuccessMessage] = useState("");

  /**
   * 次へボタンが押された時の処理
   * @param event 
   */
  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError("");

    try {
      await handleSignInFlow(username, smsCode, onNextStep);
    } catch (err: any) {
      setError(err.message);
    }
  };

  /**
   * SMS 認証コードを再送信する関数
   * TODO: ただし、未検証のコードです
   */
  const handleResendCode = async () => {
    setError("");
    setSuccessMessage("");
    
    try {
      await signIn({ username });
      setSuccessMessage("認証コードを再送しました。");
    } catch (err: any) {
      setError(err.message || "認証コードの再送に失敗しました");
    }
  };

  return (
    <div>
      <h2>SMS 認証コードを入力</h2>
      <form onSubmit={handleSubmit}>
        <label>認証コード:</label>
        <input
          type="text"
          value={smsCode}
          onChange={(e) => setSmsCode(e.target.value)}
          required
        />
        <button type="submit">次へ</button>
      </form>
      <button onClick={handleResendCode} style={{ marginTop: "10px" }}>
        認証コードを再送信
      </button>
      {successMessage && <p style={{ color: "green" }}>{successMessage}</p>}
      {error && <p style={{ color: "red" }}>{error}</p>}
    </div>
  );
};
