<?php

namespace App\Services;

use App\Models\Topic;
use Illuminate\Support\Facades\Log;

class TopicService
{
    /**
     * トピックを新規作成する
     *
     * @param  int    $userId  投稿者のユーザーID
     * @param  string $title   トピックタイトル
     * @param  string $content トピック本文
     * @return bool true: 作成成功、false: 作成失敗
     */
    public function create(int $userId, string $title, string $content): bool
    {
        try {
            $topic          = new Topic();
            $topic->user_id = $userId;
            $topic->title   = $title;
            $topic->content = $content;
            $topic->save();
        } catch (\Exception $e) {
            Log::error('トピックの作成に失敗しました', ['user_id' => $userId, 'exception' => $e]);
            return false;
        }

        return true;
    }

    /**
     * トピック本文を更新する
     *
     * @param  Topic  $topic   更新対象のトピック
     * @param  string $content 更新後の本文
     * @return bool true: 更新成功、false: 更新失敗
     */
    public function updateContent(Topic $topic, string $content): bool
    {
        try {
            $topic->content = $content;
            $topic->save();
        } catch (\Exception $e) {
            Log::error('トピックの更新に失敗しました', ['topic_id' => $topic->id, 'exception' => $e]);
            return false;
        }

        return true;
    }

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
        } catch (\Exception $e) {
            Log::error('トピックの削除に失敗しました', ['topic_id' => $topicId, 'exception' => $e]);
            return false;
        }

        return true;
    }
}
