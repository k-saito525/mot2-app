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
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class MailPasswordResetMailCheck extends Mailable
{
    use Queueable, SerializesModels;

    private $user;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // 環境ごとに送信元を設定
        $from = config('mail.from')[App::environment()]['address'];

        return new Envelope(
            from: $from,
            subject: __('mails.password_reset_mail_check.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // ユーザーのアクセスキーを取得
        $token_param = ['reset_token' => $this->user->reset_password_access_key];
        // 有効期限24時間のURLを生成
        $now = Carbon::now();
        $url = URL::temporarySignedRoute('password.reset.show.form-password', $now->addHours(24), $token_param);

        return new Content(
            view: 'mails.password.reset',
            with: [
                'user' => $this->user,
                'url' => $url,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
