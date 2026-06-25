<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comments';

    protected $fillable = [
        'comment',
        'topic_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'topic_id' => 'integer',
            'user_id'  => 'integer',
        ];
    }

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * トピックIDに紐づくコメント一覧を取得する
     *
     * @param  int $topic_id トピックID
     * @return Collection<int, static>
     */
    public function getCommentsByTopicID(int $topic_id): Collection
    {
        $comments = static::query()
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('topics', 'comments.topic_id', '=', 'topics.id')
            ->select($this->columns)
            ->where('comments.topic_id', $topic_id)
            ->orderBy('comments.created_at', 'asc')
            ->get();
        foreach ($comments as $comment) {
            $comment->comment = Topic::makeLink($comment->comment ?? '');
        }
        return $comments;
    }

    /**
     * IDを指定してコメントを1件取得する
     *
     * @param  int $comment_id コメントID
     * @return ?static null: 対象コメントなし
     */
    public function getCommentsByID(int $comment_id): ?static
    {
        return static::query()
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('topics', 'comments.topic_id', '=', 'topics.id')
            ->select($this->columns)
            ->where('comments.id', $comment_id)
            ->first();
    }

    /**
     * コメントを削除する
     *
     * @param  int $comment_id コメントID
     * @return bool true: 削除成功
     */
    public function deleteComments(int $comment_id): bool
    {
        $comment = self::find($comment_id);
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
