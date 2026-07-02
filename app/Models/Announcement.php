<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    public function scopePublished(Builder $query): Builder
    {
        $now = now()->toDateString();
        return $query->where('pub_start_at', '<=', $now)
            ->where(fn($q) => $q->whereNull('pub_end_at')->orWhere('pub_end_at', '>=', $now));
    }

    /**
     * お知らせ一覧を取得する
     *
     * @param  bool  $only_id true の場合はIDのみ取得
     * @param  array $target  取得対象のお知らせIDの配列（空の場合は全件）
     * @return array<int, array>
     */
    public function getAnnouncements(bool $only_id = false, array $target = []): array
    {
        $query = static::query();
        if ($only_id === true) {
            $query->select('id');
        }
        if (!empty($target)) {
            $query->whereIn('id', $target);
        }
        return $query->get()->toArray();
    }

}

