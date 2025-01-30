<h1>SMS MFA 設定</h1>

@if(session('success'))
    <div style="color:green;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div style="color:red;">{{ session('error') }}</div>
@endif

@if($phoneNumber)
<p>登録済み電話番号: {{ $phoneNumber }}
    @if($phoneVerified)
        <span style="color: green;">検証済</span>
    @else
        <span style="color: red;">未検証</span>
    @endif
</p>
@else
<p>登録済み電話番号: なし</p>
@endif

@if($hasSmsMfa)
    <p>SMS MFA は有効です。</p>
@else
    <p>SMS MFA は無効です。</p>
    @if($phoneVerified)
        <form action="{{ route('mfa.sms.enable') }}" method="POST">
            @csrf
            <button type="submit">SMS MFAを有効にする</button>
        </form>
    @else
        <form action="{{ route('mfa.sms.phone_update') }}" method="POST">
            @csrf
            @if ($errors->has('phone_number'))
                <div style="color: red;">
                    {{ $errors->first('phone_number') }}
                </div>
            @endif
            <label for="phone_number">電話番号 (E.164形式)</label>
            <input type="text" name="phone_number" id="phone_number" placeholder="+81XXXXXXXXXX" required>

            <button type="submit">送信</button>
        </form>
    @endif
@endif
