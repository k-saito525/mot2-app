<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Topic;

class Comment extends Model
{
    use HasFactory;

    // テーブル名の定義
    protected $table = 'comments';

    /**
     * 登録や更新を許可しないカラムを設定
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * カラムの型定義(データ取得時に指定の型で取得する)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'comment' => 'string',
        'topic_id' => 'integer',
        'user_id' => 'integer',
    ];

    // 表示用カラム
    private $columns = [
        'comments.id',
        'comments.comment',
        'comments.created_at',
        'comments.updated_at',
        'topics.id as topic_id',
        'users.id as user_id',
        'users.name as username',
        'users.user_icon',
        'users.user_identifier',
    ];

    /**
     * トピックに紐づくコメントを取得
     * 
     * @param int $topic_id  トピックID
     */
    public function getCommentsByTopicID(int $topic_id = 0)
    {
        // 古いコメントを上位に表示するため作成日時順に取得
        $comments = DB::table($this->table)
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('topics', 'comments.topic_id', '=', 'topics.id')
            ->select($this->columns)
            ->where('comments.topic_id', $topic_id)
            ->whereNull('comments.deleted_at')
            ->orderBy('comments.created_at', 'asc')
            ->get();

        if ($comments->isNotEmpty()) {
            // 本文内のURLをリンクにする
            foreach ($comments as $comment) {
                $comment->comment = Topic::makeLink(data_get($comment, 'comment', ''));
            }
        }
        return $comments;
    }

    /**
     * IDをもとにコメントを取得
     * → コメント編集画面で使用
     * 
     * @param int|string $comment_id  トピックID
     */
    public function getCommentsByID(int|string $comment_id)
    {
        // 古いコメントを上位に表示するため作成日時順に取得
        $comments = DB::table($this->table)
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('topics', 'comments.topic_id', '=', 'topics.id')
            ->select($this->columns)
            ->where('comments.id', $comment_id)
            ->whereNull('comments.deleted_at')
            ->first();

        return $comments;
    }

    /**
     * トピックに紐づくコメントを削除
     * 
     * @param int|string $comment_id  トピックID
     */
    public function deleteComments(int|string $comment_id)
    {
        // 削除対象のコメント取得
        $comment = self::find($comment_id);

        if (!empty($comment)) {
            // 削除日時
            $delete_time = now();
            $comment->deleted_at = $delete_time;
            try {
                $comment->save();
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }
}
