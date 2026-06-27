<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Support extends Model
{
    use HasFactory, SoftDeletes;

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
     * メッセージ一覧を取得する
     *
     * @return Collection<int, static>
     */
    public function getMessages(): Collection
    {
        return static::query()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * IDを指定してメッセージを1件取得する
     *
     * @param  int $id supportsテーブルのID
     * @return ?static null: 対象メッセージなし
     */
    public function getMessageById(int $id): ?static
    {
        return static::query()
            ->with('user')
            ->find($id);
    }
}
