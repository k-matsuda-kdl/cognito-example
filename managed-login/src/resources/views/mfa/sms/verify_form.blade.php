<h1>電話番号の検証</h1>
<p>SMSで送られた認証コードを入力してください。</p>

<form action="{{ route('mfa.sms.verify_code') }}" method="POST">
    @csrf
    @if ($errors->has('verification_code'))
        <div style="color: red;">
            {{ $errors->first('verification_code') }}
        </div>
    @endif
    <label for="verification_code">検証コード:</label>
    <input type="text" name="verification_code" id="verification_code" required>

    <button type="submit">検証</button>
</form>
