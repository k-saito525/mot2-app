<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\AnnouncementRead;

class Announcement extends Model
{
    use HasFactory;

    // テーブル名の定義
    protected $table = 'announcements';

    /**
     * カラムの型定義(データ取得時に指定の型で取得する)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'content' => 'string',
        'user_id' => 'integer',
    ];

    /**
     * お知らせ取得
     * 
     * @param  bool  $only_id IDのみを取得する場合はtrue
     * @param  string $target 取得したいお知らせID
     * @return array $res
     */
    public function getAnnouncements(bool $only_id = false, array $target = []): array
    {
        $query = DB::table($this->table);
        if ($only_id === true) {
            /* IDのみ取得 */
            $query->select('id');
        }
        if (!empty($target)) {
            /* 指定のIDがある場合 */
            $query->whereIn('id', $target);
        }
        $res = $query->whereNull('deleted_at')
            ->get()
            ->toArray();
        return $res;
    }

    /**
     * 公開中のお知らせのみを取得
     * 
     * @param bool $only_id IDのみを取得する場合true
     * @return array $res
     */
    public function getPublicAnnouncements(bool $only_id = false): array
    {
        $res = [];
        // 現在時刻
        $now = date('Y-m-d');
        // 一旦公開開始済みのお知らせを全て取得
        $tmp = DB::table($this->table)
            ->whereDate('pub_start_at', '<=', $now)
            ->whereNull('deleted_at')
            ->get()
            ->toArray();
        // 取得結果の中に公開終了しているお知らせがあったら削除
        foreach ($tmp as $key => $val) {
            if (!empty(data_get($val, 'pub_end_at')) && $now > data_get($val, 'pub_end_at', '')) {
                continue;
            } else {
                if ($only_id === true) {
                    /* IDのみの配列になるようにする */
                    array_push($res, data_get($val, 'id'));
                } else {
                    $res[] = $val;
                }
            }
        }

        return $res;
    }

    /**
     * 公開中のお知らせを公開ステータスと既読数を含めて取得
     * 
     * @param bool $only_id IDのみを取得する場合はtrue
     */
    public function getStatusRead(string|int $user_id)
    {
        // 公開中のお知らせIDを取得
        $announcement_ids = self::getPublicAnnouncements(true);

        if (!empty($announcement_ids)) {
            // 公開中お知らせの中で既読数を取得
            $m_announcement_read = new AnnouncementRead();
            $read_info = $m_announcement_read->getCount($user_id, $announcement_ids);
            // お知らせリストに既読のステータスを追加 ※未読の場合はキー自体作成しない
            $announcement_list['unread_count'] = count($announcement_ids) - data_get($read_info, 'read_count');
            $announcement_list['announcement'] = self::getPublicAnnouncements();
            foreach ($announcement_list['announcement'] as $a_key => $a_val) {
                foreach (data_get($read_info, 'id', []) as $r_key => $r_id)
                    if ($a_val->id === data_get($r_id, 'announcement_id')) {
                        /* 既読 */
                        $announcement_list['announcement'][$a_key]->pub_status = 1;
                    }
            }
        }

        // 公開中のお知らせが1件も無い場合の返却用
        if (!isset($announcement_list)) {
            $announcement_list = [
                'unread_count' => 0,
                'announcement' => '',
            ];
        }
        return $announcement_list;
    }
}
