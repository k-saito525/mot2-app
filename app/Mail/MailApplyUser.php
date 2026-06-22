<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

/*
 * ユーザー会員登録申請完了時のメール送信 - ユーザーへの送信
 */

class MailApplyUser extends Mailable
{
    use Queueable, SerializesModels;

    private $user = [];

    /**
     * Create a new message instance.
     */
    public function __construct(array $user_data)
    {
        if (!empty($user_data['past-join'])) {
            $user_data['past-join'] = implode(',', $user_data['past-join']);
        } else {
            $user_data['past-join'] = '';
        }
        $this->user = $user_data;
    }

    /**
     * Get the message envelope.
     * 送信元アドレス、送信元名
     */
    public function envelope(): Envelope
    {
        // 環境ごとに送信元を設定
        $from = config('mail.from')[App::environment()]['address'];

        return new Envelope(
            from: $from,
            subject: __('mails.apply.user.subject'),
        );
    }

    /**
     * Get the message content definition.
     * メール本文の設定
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.apply.user',
            with: [
                'user' => $this->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     * ※メールに添付ファイルがある場合に使用するため、不要
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
