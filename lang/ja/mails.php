<?php

declare(strict_types=1);

return [
    'only_send_first'  => '※本メールは配信専用のアドレスで配信されています。',
    'only_send_second' => '※本メールに返信いただいても内容の確認及び返答はできませんので、ご了承ください。',
    /* ユーザー会員登録申請 */
    'apply' => [
        'user' => [
            'subject' => '【MOT2】会員登録申請を承りました。',

        ],
        'admin' => [
            'subject' => '【MOT2】会員登録申請が届いています。',
        ],
        'approved' => [
            'subject' => '【MOT2】会員登録申請が承認されました。',
        ]
    ],
    /* パスワードリセット(非ログイン時) */
    'password_reset_mail_check' => [
        'subject' => '【MOT2】パスワードの再設定をお願いします。',
    ],
    /* 運営へのメッセージ */
    'support' => [
        'subject_admin' => '【MOT2】会員からメッセージが届きました。',
    ],
    /* メールアドレス変更 */
    'change_email' => [
        'subject' => '【MOT2】メールアドレスが変更されました。',
    ],
    /* コメント */
    'comment' => [
        'subject' => '【MOT2】投稿したトピックに回答されました。',
    ],
];
