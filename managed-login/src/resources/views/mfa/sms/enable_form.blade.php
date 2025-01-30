<h1>SMSによるMFAを有効化</h1>
<p>電話番号が検証済みの場合、このボタンを押すとSMSによるMFAが有効になります。</p>

<form action="{{ route('mfa.sms.enable') }}" method="POST">
    @csrf
    <button type="submit">SMS MFAを有効にする</button>
</form>
