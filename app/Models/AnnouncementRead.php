<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AnnouncementRead extends Model
{
    use HasFactory;

    // テーブル名の定義
    protected $table = 'announcement_reads';

    // 複合主キー (user_id + announcement_id) のため id カラムは存在しない
    // Eloquent は複合主キー非対応のため find() は使用不可
    protected $primaryKey = null;
    public $incrementing = false;

    // created_at のみ存在するためupdated_atは無効化
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'announcement_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * お知らせIDをもとに既読のお知らせIDと既読数を取得する
     *
     * @param  int   $user_id         ユーザーID
     * @param  array $announcement_id 対象お知らせIDの配列
     * @return array{ id: array, read_count: int }
     */
    public function getCount(int $user_id, array $announcement_id): array
    {
        $reads = static::query()
            ->where('user_id', $user_id)
            ->whereIn('announcement_id', $announcement_id)
            ->get();

        return [
            'id'         => $reads->toArray(),
            'read_count' => $reads->count(),
        ];
    }

    /**
     * お知らせを既読にする
     *
     * @param  int $announcement_id 既読にするお知らせID
     * @return bool true: 登録成功または既に既読、false: 登録失敗
     */
    public function storeReadStatus(int $announcement_id): bool
    {
        // ユーザーID
        $user_id = Auth::id();

        try {
            static::query()->firstOrCreate([
                'user_id'         => $user_id,
                'announcement_id' => $announcement_id,
            ]);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * お知らせIDに紐づく既読レコードを削除する
     *
     * @param  int $announcement_id お知らせID
     * @return void
     */
    public function deleteReadsByAnnouncementId(int $announcement_id): void
    {
        static::query()
            ->where('announcement_id', $announcement_id)
            ->delete();
    }
}
