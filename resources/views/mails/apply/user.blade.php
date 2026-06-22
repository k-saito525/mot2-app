<p>
    {{ $user['name'] }} 様<br>
    <br>
    MOT2への会員登録申請ありがとうございます。<br>
    管理者が確認し、改めてご連絡させていただきます。
</p>
<p>
    【送信内容】<br>
    お名前：{{ Arr::get($user, 'name') }}<br>
    メールアドレス：{{ Arr::get($user, 'email') }}<br>
    過去のIIMS活動参加歴：{{ Arr::get($user, 'past-join') }}
</p>
<br>
<br>
<p>
    {{ __('mails.only_send_first') }}<br>
    {{ __('mails.only_send_second') }}
</p>