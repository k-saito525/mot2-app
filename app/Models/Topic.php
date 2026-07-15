<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * 投稿者を事前読み込みするスコープ
     *
     * @param  Builder $query クエリビルダ
     * @return Builder
     */
    public function scopeWithAuthor(Builder $query): Builder
    {
        return $query->with('user');
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
        return preg_replace(
            '/(https?)(:\/\/[a-zA-Z0-9+$;?.%,!#~*\/:@&=_-]+)/',
            '<a class="content-link" href="$1$2">$1$2</a>',
            $escaped
        ) ?? $escaped;
    }
}
