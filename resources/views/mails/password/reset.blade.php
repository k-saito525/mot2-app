<p>
    {{ $user->name }} 様<br>
    <br>
    ご利用ありがとうございます。<br>
    以下URLより、パスワードの変更をお願いします。
</p>
<p>
    ============================<br>
    <a href="{{ $url }}">パスワードのご登録はこちら</a><br>
    ※上記URLの有効期限は24時間です。<br>
    ※有効期限が過ぎてしまった場合はお手数ですが再度お手続きをお願いします。<br>
    <a href="{{ route('password.reset.show.form-mail') }}">パスワードの再手続きはこちら</a><br>
    ============================
</p>
<br>
<br>
<p>
    {{ __('mails.only_send_first') }}<br>
    {{ __('mails.only_send_second') }}
</p>