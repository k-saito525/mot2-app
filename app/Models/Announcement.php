<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
     * 公開中のお知らせを公開ステータスと既読数を含めて取得
     *
     * @param  string|int  $user_id ユーザーID
     * @return array{ unread_count: int, announcement: array|string }
     */
    public function getStatusRead(string|int $user_id): array
    {
        // 公開中のお知らせIDを取得
        $announcement_ids = $this->getPublicAnnouncements(true);

        if (!empty($announcement_ids)) {
            $m_announcement_read = new AnnouncementRead();
            $read_info           = $m_announcement_read->getCount($user_id, $announcement_ids);
            $read_count          = data_get($read_info, 'read_count', 0);
            $announcement_list   = [
                'unread_count' => count($announcement_ids) - $read_count,
                'announcement' => $this->getPublicAnnouncements(),
            ];
            // 既読のお知らせに pub_status を付与する（未読はキー自体作成しない）
            foreach ($announcement_list['announcement'] as $a_key => $a_val) {
                foreach (data_get($read_info, 'id', []) as $r_id) {
                    if ($a_val->id === data_get($r_id, 'announcement_id')) {
                        $announcement_list['announcement'][$a_key]->pub_status = 1;
                    }
                }
            }
            return $announcement_list;
        }

        return [
            'unread_count' => 0,
            'announcement' => '',
        ];
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
        // 取得結果の中に公開終了しているお知らせがあったら除外
        foreach ($tmp as $val) {
            if (!empty(data_get($val, 'pub_end_at')) && $now > data_get($val, 'pub_end_at', '')) {
                continue;
            }
            if ($only_id === true) {
                /* IDのみの配列になるようにする */
                $res[] = data_get($val, 'id');
            } else {
                $res[] = $val;
            }
        }

        return $res;
    }

}
