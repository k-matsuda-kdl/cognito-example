import React, { useState, useEffect } from "react";
import { LoginFirst } from "./LoginFirst";
import { LoginStepPassword } from "./LoginStepPassword";
import { LoginStepTOTP } from "./LoginStepTOTP";
import { LoginStepSMS } from "./LoginStepSMS";
import { useNavigate } from "react-router-dom";
import { LoginStepPasskey } from "./LoginStepPasskey";

/**
 * Login画面全体のコンポーネント
 * @returns 
 */
export const Login: React.FC = () => {
  const [username, setUsername] = useState(""); // ユーザー名
  // ステップ管理
  const [step, setStep] = useState<"FIRST" | "PASSWORD" | "SMS" | "TOTP" | "WEB_AUTHN" | "LOGGED_IN">("FIRST");
  const navigate = useNavigate();

  /**
   * 次のStepに進む処理
   * @param enteredUsername 
   * @param nextChallenge 
   */
  const handleNextStep = (enteredUsername: string, nextChallenge: string) => {
    setUsername(enteredUsername);
    setStep(nextChallenge as any);
  };

  // 初期化: "LOGGED_IN" の場合は、ログインが完了しているので "/" にリダイレクト
  useEffect(() => {
    if (step === "LOGGED_IN") {
      navigate("/");
    }
  }, [step, navigate]);

  return (  
    <div>
      {step === "FIRST" && <LoginFirst onNextStep={handleNextStep} />}
      {step === "PASSWORD" && <LoginStepPassword username={username} onNextStep={handleNextStep} />}
      {step === "SMS" && <LoginStepSMS username={username} onNextStep={handleNextStep} />}
      {step === "TOTP" && <LoginStepTOTP username={username} onNextStep={handleNextStep} />}
      {step === "WEB_AUTHN" && <LoginStepPasskey username={username} onNextStep={handleNextStep} />}
    </div>
  );
};
