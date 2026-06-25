<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'topics';

    protected $fillable = [
        'title',
        'content',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    private $columns = [
        'topics.id',
        'topics.title',
        'topics.content',
        'topics.user_id',
        'topics.created_at',
        'topics.updated_at',
        'users.name',
        'users.user_icon',
        'users.user_identifier',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * トピック一覧を取得する
     *
     * @param  ?int $limit 取得件数（null の場合は全件）
     * @return Collection<int, static>
     */
    public function getTopics(?int $limit = null): Collection
    {
        $query = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select($this->columns)
            ->orderBy('topics.created_at', 'desc');
        if ($limit !== null) {
            $query = $query->limit($limit);
        }
        $topics = $query->get();
        foreach ($topics as $topic) {
            $topic->content = self::makeLink($topic->content ?? '');
        }
        return $topics;
    }

    /**
     * トピック一覧と総件数を取得する
     *
     * @param  ?int $limit  取得件数
     * @param  ?int $offset 取得開始位置
     * @return array{ topics: array, cnt: int }
     */
    public function getTopicsList(?int $limit = null, ?int $offset = null): array
    {
        $topic_info = [];
        $query = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select($this->columns)
            ->orderBy('topics.created_at', 'desc');
        if ($limit !== null) {
            $query = $query->limit($limit);
        }
        if ($offset !== null) {
            $query = $query->offset($offset);
        }
        $topics = $query->get();
        foreach ($topics as $topic) {
            $topic->content = self::makeLink($topic->content ?? '');
        }
        $topic_info['topics'] = $topics->all();
        $topic_info['cnt']    = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->count();
        return $topic_info;
    }

    /**
     * IDを指定してトピックを1件取得する
     *
     * @param  int  $topic_id トピックID
     * @param  bool $flg_link content 内のURLをリンクに変換するか
     * @return ?static null: 対象トピックなし
     */
    public function getTopicById(int $topic_id, bool $flg_link = true): ?static
    {
        $topic = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select($this->columns)
            ->where('topics.id', $topic_id)
            ->first();
        if ($flg_link === true && $topic !== null && !empty($topic->content)) {
            $topic->content = self::makeLink($topic->content);
        }
        return $topic;
    }

    /**
     * ユーザーIDに紐づくトピック一覧を取得する
     *
     * @param  int $user_id ユーザーID
     * @return Collection<int, static>
     */
    public function getTopicByUser(int $user_id): Collection
    {
        $topics = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select($this->columns)
            ->where('topics.user_id', $user_id)
            ->get();
        foreach ($topics as $topic) {
            $topic->content = self::makeLink($topic->content ?? '');
        }
        return $topics;
    }

    /**
     * テキスト内のURLをaタグに変換する
     *
     * @param  string $content 変換対象のテキスト
     * @return string
     */
    public static function makeLink(string $content = ''): string
    {
        if (empty($content)) {
            return '';
        }
        return mb_ereg_replace(
            "(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)",
            '<a class="content-link" href="\1\2">\1\2</a>',
            $content
        );
    }
}
