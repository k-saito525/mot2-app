<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailComment extends Mailable
{
    use Queueable, SerializesModels;

    // 回答されたトピックのID
    private $topic_id;
    // トピックの作成者
    private $topic_author;
    // コメント主
    private $comment_author;

    /**
     * Create a new message instance.
     */
    public function __construct($topic_author, $comment_author, $topic_id)
    {
        $this->topic_id = $topic_id;
        $this->topic_author = $topic_author;
        $this->comment_author = $comment_author;
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
            subject: __('mails.comment.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.comment.topicauthor',
            with: [
                'topic_id' => $this->topic_id,
                'topic_author' => $this->topic_author,
                'comment_author' => $this->comment_author,
            ],
        );
    }
}
