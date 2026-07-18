<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Support extends Model
{
    use HasFactory;

    // テーブル名の定義
    protected $table = 'supports';

    protected $fillable = [
        'message',
        'user_id',
    ];

    /**
     * カラムの型定義(データ取得時に指定の型で取得する)
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
