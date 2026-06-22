<p>
    会員よりメッセージが届きました。<br>
    下記よりご確認ください。
</p>
<p>
    ============================<br>
    <a href="{{ route('admin.show.support.list') }}">メッセージの一覧はこちら</a><br>
    ============================
</p>
<!-- <p>
    【送信内容】<br>
    メッセージ：{!! nl2br(htmlspecialchars(data_get($message, 'message'))) !!}
</p> -->
<br>
<br>
<p>
    {{ __('mails.only_send_first') }}<br>
    {{ __('mails.only_send_second') }}
</p>