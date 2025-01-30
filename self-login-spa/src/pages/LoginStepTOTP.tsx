import React, { useState } from "react";
import { handleSignInFlow } from "../services/authService";

type Props = {
  username: string;
  onNextStep: (username: string, nextChallenge: string) => void;
};

/**
 * TOTP 認証のステップ
 * @param {username, onNextStep}
 * @returns 
 */
export const LoginStepTOTP: React.FC<Props> = ({ username, onNextStep }) => {
  const [totpCode, setTotpCode] = useState("");
  const [error, setError] = useState("");

  /**
   * 次へボタンが押された時の処理
   * @param event 
   */
  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError("");

    try {
      await handleSignInFlow(username, totpCode, onNextStep);
    } catch (err: any) {
      setError(err.message);
    }
  };

  return (
    <div>
      <h2>Authenticator アプリのコードを入力</h2>
      <form onSubmit={handleSubmit}>
        <label>TOTP コード:</label>
        <input
          type="text"
          value={totpCode}
          onChange={(e) => setTotpCode(e.target.value)}
          required
        />
        <button type="submit">次へ</button>
      </form>
      {error && <p style={{ color: "red" }}>{error}</p>}
    </div>
  );
};
