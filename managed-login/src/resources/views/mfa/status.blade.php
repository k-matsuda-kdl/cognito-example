<h1>MFAステータス確認</h1>

<p>優先するMFA : 
    @if($preferredMfaSetting === 'SMS_MFA')
        SMSメッセージ
    @elseif($preferredMfaSetting === 'SOFTWARE_TOKEN_MFA')
        Authenticatorアプリ
    @else
        なし
    @endif
</p>

<!-- ユーザーが持っているMFAオプション: SMS, SOFTWARE_TOKEN -->
<!-- 例: $hasSoftwareTokenMfa, $hasSmsMfa -->
@if($hasSoftwareTokenMfa || $hasSmsMfa)
    <p>優先MFAを変更:</p>

    <form action="{{ route('mfa.updatePreference') }}" method="POST">
        @csrf
        <select name="mfa_preferred">
            @if($hasSmsMfa)
                <option value="SMS_MFA"
                    @if($preferredMfaSetting === 'SMS_MFA') selected @endif>
                    SMSメッセージ
                </option>
            @endif

            @if($hasSoftwareTokenMfa)
                <option value="SOFTWARE_TOKEN_MFA"
                    @if($preferredMfaSetting === 'SOFTWARE_TOKEN_MFA') selected @endif>
                    Authenticatorアプリ
                </option>
            @endif

            <!-- もし「MFAオフ」を選べるなら以下のような項目を追加 -->
            <option value="NONE"
                @if(!$preferredMfaSetting) selected @endif>
                なし
            </option>
        </select>
        @if ($errors->has('mfa_preferred'))
            <div style="color: red;">
                {{ $errors->first('mfa_preferred') }}
            </div>
        @endif
        <button type="submit">更新</button>
    </form>
@endif

<p>Authenticatorアプリ (TOTP) :
    @if($hasSoftwareTokenMfa)
        <span style="color: green;">有効</span> <a onclick="return confirm('Authenticatorアプリを無効にしますか？')" href="{{ route('mfa.totp.disable') }}">無効にする</a>
    @else
        <span style="color: red;">無効</span> <a href="{{ route('mfa.totp.setup') }}">設定</a>
    @endif
</p>

<p>SMSメッセージ:
    @if($hasSmsMfa)
        <span style="color: green;">有効</span> <a onclick="return confirm('SMSのMFAを無効にしますか？')" href="{{ route('mfa.sms.disable') }}">無効にする</a>
    @else
        <span style="color: red;">無効</span> <a href="{{ route('mfa.sms.phone_form') }}">設定</a>

    @endif
</p>




