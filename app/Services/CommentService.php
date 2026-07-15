<?php

namespace App\Services;

use App\Mail\MailComment;
use App\Models\Comment;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommentService
{
    /**
     * コメントを新規作成する
     *
     * トピック投稿者と異なるユーザーがコメントした場合は、投稿者に通知メールを送信する。
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

            if ($author->id !== $topic->user_id) {
                $topicAuthor = User::approved()->find((int)$topic->user_id);
                if ($topicAuthor !== null) {
                    Mail::to($topicAuthor->email)->send(new MailComment($topicAuthor, $author, $topic->id));
                }
            }
        } catch (\Exception) {
            return false;
        }

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
        } catch (\Exception) {
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
            } catch (\Exception) {
                return false;
            }
        }
        return true;
    }
}
