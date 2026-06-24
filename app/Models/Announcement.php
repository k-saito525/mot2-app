<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

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
     * お知らせ取得
     *
     * @param  bool  $only_id IDのみを取得する場合はtrue
     * @param  array $target  取得したいお知らせIDの配列
     * @return array
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

    /**
     * 公開中のお知らせを公開ステータスと既読数を含めて取得
     *
     * @param  string|int  $user_id ユーザーID
     * @return array{ unread_count: int, announcement: array|string }
     */
    public function getStatusRead(string|int $user_id): array
    {
        $announcements = $this->getPublicAnnouncements();

        if ($announcements->isEmpty()) {
            return ['unread_count' => 0, 'announcement' => ''];
        }

        $announcement_ids = $announcements->pluck('id')->all();
        $read_info        = (new AnnouncementRead())->getCount($user_id, $announcement_ids);
        $read_count       = Arr::get($read_info, 'read_count', 0);
        $read_ids         = collect(Arr::get($read_info, 'id', []))
            ->map(fn($r) => data_get($r, 'announcement_id'))
            ->all();

        // 既読のお知らせに pub_status を付与する（未読はキー自体作成しない）
        foreach ($announcements as $announcement) {
            if (in_array($announcement->id, $read_ids)) {
                $announcement->pub_status = 1;
            }
        }

        return [
            'unread_count' => count($announcement_ids) - $read_count,
            'announcement' => $announcements->all(),
        ];
    }

    /**
     * 公開中のお知らせのみを取得
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPublicAnnouncements(): \Illuminate\Database\Eloquent\Collection
    {
        $now = now()->toDateString();
        return static::where('pub_start_at', '<=', $now)
            ->where(fn($q) => $q->whereNull('pub_end_at')->orWhere('pub_end_at', '>=', $now))
            ->get();
    }

}
