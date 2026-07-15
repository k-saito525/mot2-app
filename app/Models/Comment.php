<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * 投稿者を事前読み込みするスコープ
     *
     * @param  Builder $query クエリビルダ
     * @return Builder
     */
    public function scopeWithAuthor(Builder $query): Builder
    {
        return $query->with('user');
    }
}
