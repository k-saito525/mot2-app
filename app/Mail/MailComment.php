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
    private $topicId;
    // トピックの作成者
    private $topicAuthor;
    // コメント主
    private $commentAuthor;

    /**
     * Create a new message instance.
     */
    public function __construct($topicAuthor, $commentAuthor, $topicId)
    {
        $this->topicId = $topicId;
        $this->topicAuthor = $topicAuthor;
        $this->commentAuthor = $commentAuthor;
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
            view: 'mails.comment.topic-author',
            with: [
                'topic_id' => $this->topicId,
                'topic_author' => $this->topicAuthor,
                'comment_author' => $this->commentAuthor,
            ],
        );
    }
}
