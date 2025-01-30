import React, { useState } from "react";
import { handleSignInFlow } from "../services/authService";
import { signIn, confirmSignIn } from "aws-amplify/auth";

type Props = {
  username: string;
  onNextStep: (username: string, nextChallenge: string) => void;
};

/**
 * パスワード認証のステップ
 * @param {username, onNextStep}
 * @returns 
 */
export const LoginStepPassword: React.FC<Props> = ({ username, onNextStep }) => {
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");

  /**
   * 次へボタンが押された時の処理
   * @param event 
   */
  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError("");

    try {
      // パスワード認証を実行
      await handleSignInFlow(username, password, onNextStep);
    } catch (err: any) {
      // パスワード認証のみ、セッションが一度しか使えないため、再度 signIn が必要
      // TODO: おそらくバグだと思うので一次的な対応
      if (err.message === "Invalid session for the user, session can only be used once.") {
        try {
          // セッションエラーが発生した場合、signIn をやり直す
          const { nextStep } = await signIn({ username, options: { authFlowType: "USER_AUTH" } });
    
          if (nextStep.signInStep === "CONTINUE_SIGN_IN_WITH_FIRST_FACTOR_SELECTION") {
            await confirmSignIn({ challengeResponse: "PASSWORD_SRP" });
          }
    
          // 再度 `handleSignInFlow` を実行
          await handleSignInFlow(username, password, onNextStep);
        } catch (signInErr: any) {
          setError(signInErr.message || "ログインに失敗しました。");
        }
      } else {
        setError(err.message || "ログインに失敗しました。");
      }
    }
  };

  return (
    <div>
      <h2>パスワードを入力</h2>
      <form onSubmit={handleSubmit}>
        <label>パスワード:</label>
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />
        <button type="submit">次へ</button>
      </form>
      {error && <p style={{ color: "red" }}>{error}</p>}
    </div>
  );
};
