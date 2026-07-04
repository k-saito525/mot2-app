<?php

namespace App\Services;

use App\Models\Topic;

class TopicService
{
    /**
     * トピックを削除する
     *
     * 関連するコメントも合わせてソフトデリートする。
     *
     * @param  int  $topicId 削除対象のトピックID
     * @return bool true:削除成功、false:対象なし or 削除失敗
     */
    public function delete(int $topicId): bool
    {
        $topic = Topic::find($topicId);
        if (!$topic) {
            return false;
        }

        try {
            $topic->comments()->delete();
            $topic->delete();
        } catch (\Exception) {
            return false;
        }

        return true;
    }
}
