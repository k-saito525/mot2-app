<?php

namespace App\Services;

use App\Mail\MailComment;
use App\Models\Comment;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CommentService
{
    /**
     * コメントを新規作成する
     *
     * トピック投稿者と異なるユーザーがコメントした場合は、投稿者に通知メールを送信する。
     * 通知メールの送信失敗はコメント作成自体の失敗とはしない。
     *
     * @param  Topic  $topic       コメント対象のトピック
     * @param  User   $author      コメント投稿者
     * @param  string $commentText コメント本文
     * @return bool true: 作成成功、false: 作成失敗
     */
    public function create(Topic $topic, User $author, string $commentText): bool
    {
        try {
            $comment           = new Comment();
            $comment->comment  = $commentText;
            $comment->topic_id = $topic->id;
            $comment->user_id  = $author->id;
            $comment->save();
        } catch (\Exception $e) {
            Log::error('コメントの作成に失敗しました', ['topic_id' => $topic->id, 'user_id' => $author->id, 'exception' => $e]);
            return false;
        }

        $this->notifyTopicAuthor($topic, $author);

        return true;
    }

    /**
     * コメントを更新する
     *
     * @param  Comment $comment     更新対象のコメント
     * @param  string  $commentText 更新後のコメント本文
     * @return bool true: 更新成功、false: 更新失敗
     */
    public function updateComment(Comment $comment, string $commentText): bool
    {
        try {
            $comment->comment = $commentText;
            $comment->save();
        } catch (\Exception $e) {
            Log::error('コメントの更新に失敗しました', ['comment_id' => $comment->id, 'exception' => $e]);
            return false;
        }

        return true;
    }

    /**
     * コメントを削除する
     *
     * @param  int  $commentId コメントID
     * @return bool true: 削除成功または対象なし、false: 削除失敗
     */
    public function delete(int $commentId): bool
    {
        $comment = Comment::find($commentId);
        if (!empty($comment)) {
            try {
                $comment->delete();
            } catch (\Exception $e) {
                Log::error('コメントの削除に失敗しました', ['comment_id' => $commentId, 'exception' => $e]);
                return false;
            }
        }
        return true;
    }

    /**
     * トピック投稿者へコメント通知メールを送信する
     *
     * コメント投稿者自身がトピック投稿者の場合は送信しない。
     * 送信失敗はログに残すのみで、コメント作成の成否には影響させない。
     *
     * @param  Topic $topic  コメント対象のトピック
     * @param  User  $author コメント投稿者
     * @return void
     */
    private function notifyTopicAuthor(Topic $topic, User $author): void
    {
        if ($author->id === $topic->user_id) {
            return;
        }

        $topicAuthor = User::approved()->find((int)$topic->user_id);
        if ($topicAuthor === null) {
            return;
        }

        try {
            Mail::to($topicAuthor->email)->send(new MailComment($topicAuthor, $author, $topic->id));
        } catch (\Exception $e) {
            Log::error('コメント通知メールの送信に失敗しました', [
                'topic_id'    => $topic->id,
                'to_user_id'  => $topicAuthor->id,
                'exception'   => $e,
            ]);
        }
    }
}
