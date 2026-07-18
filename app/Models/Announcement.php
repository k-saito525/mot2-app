<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property-read Carbon $pub_start_at
 * @property-write Carbon|string $pub_start_at
 * @property-read Carbon|null $pub_end_at
 * @property-write Carbon|string|null $pub_end_at
 * @property bool|null $is_read DBカラムではなく、既読/未読判定のためAnnouncementService::getStatusRead()が実行時に付与する一時プロパティ
 */
class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    // テーブル名の定義
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'pub_start_at',
        'pub_end_at',
        'publish_status',
    ];

    /**
     * カラムの型定義(データ取得時に指定の型で取得する)
     */
    protected function casts(): array
    {
        return [
            'user_id'        => 'integer',
            'publish_status' => 'integer',
            'pub_start_at'   => 'date',
            'pub_end_at'     => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    /**
     * 管理者向け表示用の公開ステータスラベルを返すアクセサ
     *
     * @return Attribute<string, never>
     */
    protected function pubStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                $today = now()->startOfDay();
                if (!empty($this->pub_end_at) && $this->pub_end_at->lt($today)) {
                    return '公開終了';
                }
                if ($this->pub_start_at->lte($today)) {
                    return '公開中';
                }
                return '公開前';
            }
        );
    }

    public function scopePublished(Builder $query): Builder
    {
        $now = now()->toDateString();
        return $query->where('pub_start_at', '<=', $now)
            ->where(fn($q) => $q->whereNull('pub_end_at')->orWhere('pub_end_at', '>=', $now));
    }
}

