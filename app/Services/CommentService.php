<?php

namespace App\Services;

use App\Models\Comment;

class CommentService
{
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
