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

class MailChangeEmail extends Mailable
{
    use Queueable, SerializesModels;

    // ユーザー情報
    private $user;
    // 変更前のメールアドレス
    private $oldEmail;
    /**
     * Create a new message instance.
     */
    public function __construct($user, $oldEmail)
    {
        // ユーザー情報
        $this->user = $user;
        // 変更前のメールアドレス
        $this->oldEmail = $oldEmail;
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
            subject: __('mails.change_email.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.email.change',
            with: [
                'user' => $this->user,
                'old_email' => $this->oldEmail,
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
