<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnouncementRead extends Model
{
    use HasFactory;

    const NOT_PUBLIC = 0;
    const IS_PUBLIC = 1;

    // テーブル名の定義
    protected $table = 'announcement_reads';

    // created_at のみ存在するためupdated_atは無効化
    const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * お知らせIDをもとに、既読のお知らせIDと数を取得
     *
     * @param string|int $user_id         ユーザーID
     * @param array      $announcement_id 対象お知らせIDの配列
     */
    public function getCount(string|int $user_id, array $announcement_id = []): array
    {
        $res = [];
        // 既読のお知らせID
        $res['id'] = DB::table($this->table)
            ->join('announcements', 'announcement_reads.announcement_id', '=', 'announcements.id')
            ->where('announcement_reads.user_id', $user_id)
            ->whereIn('announcement_reads.announcement_id', $announcement_id)
            ->whereNull('announcements.deleted_at')
            ->get()
            ->toArray();

        // 既読数
        $res['read_count'] = count($res['id']);

        return $res;
    }

    /**
     * お知らせを既読にする
     *
     * @param string|int $announcement_id 既読にするお知らせID
     * @return bool  true:更新成功 or 既に既読
     */
    public function storeReadStatus(string|int $announcement_id): bool
    {
        // ユーザーID
        $user_id = Auth::id();

        $exists = DB::table($this->table)
            ->where('user_id', $user_id)
            ->where('announcement_id', $announcement_id)
            ->exists();

        if (!$exists) {
            /* 未読の場合のみDB更新 */
            try {
                DB::table($this->table)
                    ->insert([
                        'user_id'         => $user_id,
                        'announcement_id' => $announcement_id,
                    ]);
                return true;
            } catch (\Exception) {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * お知らせ削除時に関連する既読レコードを削除する
     *
     * @param string|int $announcement_id お知らせID
     * @param int $flg NOT_PUBLIC(0):既読レコードを削除、IS_PUBLIC(1):何もしない
     */
    public function _update(string|int $announcement_id, int $flg): void
    {
        if ($flg === self::NOT_PUBLIC) {
            try {
                DB::table($this->table)
                    ->where('announcement_id', $announcement_id)
                    ->delete();
            } catch (\Exception) {
                //
            }
        }
    }
}
