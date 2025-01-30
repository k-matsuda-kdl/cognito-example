<h1>ようこそ、{{ Auth::user()->name }}</h1>
<p>Email: {{ Auth::user()->email }}</p>
<p><a href="{{ route('password.change') }}">パスワードを変更する</a></p>
<p><a href="{{ route('mfa.showstatus') }}">MFAを設定する</a></p>
<p><a href="{{ route('passkey.index') }}">passkeyを設定する</a></p>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
    @csrf
    <button type="submit">ログアウト</button>
</form>