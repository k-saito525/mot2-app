<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AnnouncementRead extends Model
{
    use HasFactory;

    const NOT_PUBLIC = 0;
    const IS_PUBLIC = 1;

    // テーブル名の定義
    protected $table = 'announcement_reads';

    /**
     * お知らせIDをもとに、既読のお知らせIDと数を取得
     */
    public function getCount(string|int $user_id, array $announcement_id = [])
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
    public function storeReadStatus(string|int $announcement_id)
    {
        // ユーザーID
        $user_id = Auth::id();

        $ret = DB::table($this->table)
            ->where('user_id', $user_id)
            ->where('announcement_id', $announcement_id)
            ->get()
            ->toArray();

        if (empty($ret)) {
            /* 未読の場合のみDB更新 */
            try {
                DB::table($this->table)
                    ->insert([
                        'user_id' => $user_id,
                        'announcement_id' => $announcement_id,
                    ]);
                return true;
            } catch (\Exception $e) {
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
     * @return
     */
    public function _update(string|int $announcement_id, int $flg)
    {
        if ($flg === self::NOT_PUBLIC) {
            try {
                DB::table($this->table)
                    ->where('announcement_id', $announcement_id)
                    ->delete();
            } catch (\Exception $e) {
                return to_route('404');
            }
        }
        return;
    }
}
