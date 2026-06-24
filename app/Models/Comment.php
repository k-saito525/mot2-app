<?php

namespace App\Models;

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

    public function getCommentsByTopicID(int $topic_id = 0)
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

    public function getCommentsByID(string $comment_id)
    {
        return static::query()
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('topics', 'comments.topic_id', '=', 'topics.id')
            ->select($this->columns)
            ->where('comments.id', $comment_id)
            ->first();
    }

    public function deleteComments(int|string $comment_id): bool
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
