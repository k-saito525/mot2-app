<p>
    作成したトピックに{{ data_get($comment_author, 'name') }}様より回答が投稿されました。<br>
    下記よりご確認ください。
</p>
<p>
    ============================<br>
    <a href="{{ route('topic.show.detail', ['id' => $topic_id]) }}">回答のご確認はこちら</a><br>
    ============================
</p>
<br>
<br>
<p>
    {{ __('mails.only_send_first') }}<br>
    {{ __('mails.only_send_second') }}
</p>