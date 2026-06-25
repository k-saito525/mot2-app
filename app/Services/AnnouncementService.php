<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Carbon\Carbon;
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
