<p>
    {{ $user->name }} 様<br>
    <br>
    メールアドレスの変更が完了しました。<br>
</p>
<p>
    ============================<br>
    変更前：{{ $old_email }}<br>
    ↓<br>
    変更後：{{ $user->email }}<br>
    ============================
</p>
<p>※※ 本メールにお心当たりのない場合は破棄してください。 ※※</p>
<br>
<br>
<p>
    {{ __('mails.only_send_first') }}<br>
    {{ __('mails.only_send_second') }}
</p>