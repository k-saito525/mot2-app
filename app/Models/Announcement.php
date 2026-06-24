<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        foreach ($tmp as $val) {
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

}
