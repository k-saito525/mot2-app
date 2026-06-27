<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * comment 内のURLをaタグに変換したテキストを返すアクセサ
     *
     * @return Attribute<null, string>
     */
    protected function commentFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => Topic::makeLink($this->getRawOriginal('comment') ?? '')
        );
    }

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
        return static::query()
            ->with('user')
            ->where('topic_id', $topic_id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * IDを指定してコメントを1件取得する
     *
     * @param  int $comment_id コメントID
     * @return ?static null: 対象コメントなし
     */
    public function getCommentByID(int $comment_id): ?static
    {
        return static::query()
            ->with('user')
            ->find($comment_id);
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
