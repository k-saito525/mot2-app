<p>
    {{ $user->name }} 様<br>
    <br>
    会員登録申請が承認されました。<br>
    以下URLより、ログイン用パスワードのご登録をお願いいたします。
</p>
<p>
    ============================<br>
    <a href="{{ route('password.new.show.form', ['token' => $user->verify_token]) }}">パスワードのご登録はこちら</a><br>
    ============================
</p>
<br>
<br>
<p>
    {{ __('mails.only_send_first') }}<br>
    {{ __('mails.only_send_second') }}
</p>