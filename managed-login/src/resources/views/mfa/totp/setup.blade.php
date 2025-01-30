<h1>AuthenticatorアプリでのMFA設定</h1>

<p>以下のQRコードを、Google AuthenticatorやMicrosoft Authenticator等でスキャンしてください。</p>

<img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="TOTP QR Code">

<p>もしくは手動設定する場合はシークレットキーを入力: {{ $secretCode }}</p>
<!-- エラーメッセージの表示 -->
@if ($errors->has('totp_code'))
    <div style="color: red;">
        {{ $errors->first('totp_code') }}
    </div>
@endif
<form action="{{ route('mfa.totp.verify') }}" method="POST">
    @csrf
    <label for="totp_code">表示された6桁コード:</label>
    <input type="text" name="totp_code" id="totp_code" required>

    <button type="submit">MFAを有効にする</button>
</form>
