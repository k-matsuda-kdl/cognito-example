import { signIn, confirmSignIn } from "aws-amplify/auth";

/**
 * 認証フローを共通化する関数
 * @param username - ユーザー名またはメールアドレス
 * @param challengeResponse - パスワードや認証コード (任意)
 * @param onNextStep - 次のステップに進むための関数
 */
export const handleSignInFlow = async (
  username: string,
  challengeResponse: string | null,
  onNextStep: (username: string, nextChallenge: string) => void
) => {
  try {
    let result;

    if (challengeResponse === null) {
      // 初回 signIn (メールアドレス or ユーザー名を入力)
      result = await signIn({ username, options: { authFlowType: "USER_AUTH" } });
    } else {
      // 2段階目以降 (パスワード、SMS、TOTP)
      result = await confirmSignIn({ challengeResponse });
    }

    console.log("Next Step:", result.nextStep.signInStep);

    // 全てのケースを網羅できていないことに注意してください
    // https://docs.amplify.aws/react/build-a-backend/auth/connect-your-frontend/sign-in/
    switch (result.nextStep.signInStep) {
      case "CONFIRM_SIGN_IN_WITH_SMS_CODE":
        onNextStep(username, "SMS");
        break;
      case "CONFIRM_SIGN_IN_WITH_TOTP_CODE":
        onNextStep(username, "TOTP");
        break;
			case "CONFIRM_SIGN_IN_WITH_PASSWORD":
				onNextStep(username, "PASSWORD");
				break;
      case "CONTINUE_SIGN_IN_WITH_FIRST_FACTOR_SELECTION":
        // 初回にSMS_TOTPが入る場合もあるが、ここではPASSWORD_SRPを選択
        if (result.nextStep.availableChallenges?.includes("WEB_AUTHN")) {
          onNextStep(username, "WEB_AUTHN");
        } else {
          await confirmSignIn({ challengeResponse: "PASSWORD_SRP" });
          onNextStep(username, "PASSWORD");
        }
        break;
      case "DONE":
        onNextStep(username, "LOGGED_IN");
        break;
      default:
        onNextStep(username, "PASSWORD");
        break;
    }
  } catch (err: any) {
    throw new Error(err.message || "ログインに失敗しました");
  }
};
