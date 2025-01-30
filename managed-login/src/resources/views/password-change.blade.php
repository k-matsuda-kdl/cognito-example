<h1>パスワードを変更する</h1>

@if (session('error'))
    <p style="color: red;">{{ session('error') }}</p>
@endif

<form method="POST" action="{{ route('password.change.post') }}">
    @csrf
    <label for="current_password">現在のパスワード:</label>
    <input type="password" id="current_password" name="current_password" required>

    <label for="new_password">新しいパスワード:</label>
    <input type="password" id="new_password" name="new_password" required>

    <label for="new_password_confirmation">新しいパスワード (確認):</label>
    <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>

    <button type="submit">変更する</button>
</form>
