import React, { useState } from "react";
import { handleSignInFlow } from "../services/authService";

type Props = {
  username: string;
  onNextStep: (username: string, nextChallenge: string) => void;
};

/**
 * ログインのPasskey認証のステップ
 * @param {username, onNextStep}
 * @returns 
 */
export const LoginStepPasskey: React.FC<Props> = ({ username, onNextStep }) => {
  const [error, setError] = useState("");

  /**
   * Passkey (WebAuthn) でログインするボタンが押された時の処理
   * 
   * @param event 
   */
  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError("");

    try {
      // Passkey (WebAuthn) で認証を実行
      await handleSignInFlow(username, "WEB_AUTHN", onNextStep);
    } catch (err: any) {
      setError(err.message);
    }
  };

  return (
    <div>
      <h2>Passkey (WebAuthn) で認証</h2>
      <form onSubmit={handleSubmit}>
        <button type="submit">Passkey でログイン</button>
      </form>
      {error && <p style={{ color: "red" }}>{error}</p>}
    </div>
  );
};
