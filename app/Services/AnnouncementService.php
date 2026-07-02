<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AnnouncementService
{
    /**
     * お知らせと関連する既読レコードを削除する
     *
     * @param  int $announcement_id お知らせID
     * @return bool true: 削除成功、false: 対象なし or 削除失敗
     */
    public function delete(int $announcement_id): bool
    {
        $announcement = Announcement::find($announcement_id);
        if (!$announcement) {
            return false;
        }

        try {
            DB::transaction(function () use ($announcement, $announcement_id) {
                $announcement->delete();
                (new AnnouncementRead())->deleteReadsByAnnouncementId($announcement_id);
            });
        } catch (\Throwable) {
            return false;
        }

        return true;
    }

    /**
     * 公開中のお知らせを未読数・既読状態付きで取得する
     *
     * @param  int $user_id ユーザーID
     * @return array{ unread_count: int, announcement: Announcement[]|string }
     */
    public function getStatusRead(int $user_id): array
    {
        $announcements = Announcement::published()->get();

        if ($announcements->isEmpty()) {
            return ['unread_count' => 0, 'announcement' => ''];
        }

        $announcement_ids = $announcements->pluck('id')->all();
        $read_info        = (new AnnouncementRead())->getCount($user_id, $announcement_ids);
        $read_count       = Arr::get($read_info, 'read_count', 0);
        $read_ids         = collect(Arr::get($read_info, 'reads', []))
            ->map(fn($r) => data_get($r, 'announcement_id'))
            ->all();

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
     * お知らせを保存し、公開状況に応じて既読レコードを同期する
     *
     * 公開前・公開終了の場合は既読レコードを削除する。
     *
     * @param  Announcement $announcement 保存対象のお知らせ
     * @return bool true: 保存成功、false: 保存失敗
     */
    public function saveAndSyncReads(Announcement $announcement): bool
    {
        try {
            DB::transaction(function () use ($announcement) {
                $announcement->save();
                $today = Carbon::today();
                $is_not_public = $announcement->pub_start_at->gt($today)
                    || (!empty($announcement->pub_end_at) && $announcement->pub_end_at->lt($today));
                if ($is_not_public) {
                    (new AnnouncementRead())->deleteReadsByAnnouncementId($announcement->id);
                }
            });
        } catch (\Throwable) {
            return false;
        }

        return true;
    }
}
