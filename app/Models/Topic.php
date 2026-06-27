<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
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

    /**
     * content 内のURLをaタグに変換したテキストを返すアクセサ
     *
     * @return Attribute<null, string>
     */
    protected function contentFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => self::makeLink($this->getRawOriginal('content') ?? '')
        );
    }

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
            ->with('user')
            ->orderBy('created_at', 'desc');
        if ($limit !== null) {
            $query = $query->limit($limit);
        }
        return $query->get();
    }

    /**
     * トピック一覧をページネータで取得する
     *
     * @param  int $perPage 1ページあたりの取得件数
     * @param  int $page    ページ番号
     * @return LengthAwarePaginator
     */
    public function getTopicsList(int $perPage, int $page): LengthAwarePaginator
    {
        return static::query()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * IDを指定してトピックを1件取得する
     *
     * @param  int $topic_id トピックID
     * @return ?static null: 対象トピックなし
     */
    public function getTopicById(int $topic_id): ?static
    {
        return static::query()
            ->with('user')
            ->find($topic_id);
    }

    /**
     * ユーザーIDに紐づくトピック一覧を取得する
     *
     * @param  int $user_id ユーザーID
     * @return Collection<int, static>
     */
    public function getTopicByUser(int $user_id): Collection
    {
        return static::query()
            ->with('user')
            ->where('user_id', $user_id)
            ->get();
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
        $escaped = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        return mb_ereg_replace(
            "(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)",
            '<a class="content-link" href="\1\2">\1\2</a>',
            $escaped
        );
    }
}
