<h1>Passkey List</h1>

@if($errors->any())
<div style="color:red;">
    {{ $errors->first('error') }}
</div>
@endif

@if(session('status'))
<div style="color:green;">
    {{ session('status') }}
</div>
@endif

@if(count($credentials) === 0)
    <p>パスキーは登録されていません。</p>
    <p><a href="{{ route('passkey') }}">パスキーを登録する</a></p>
@else
    <p>パスキーは登録されています。</p>

    <form action="{{ route('passkey.delete') }}" method="POST" onsubmit="return confirm('Delete this passkey?')">
        @csrf
        <input type="hidden" name="credential_id" value="{{ $credentials[0]['CredentialId'] }}">
        <button type="submit">削除する</button>
    </form>
@endif
