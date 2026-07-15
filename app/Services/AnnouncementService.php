<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnnouncementService
{
    /**
     * お知らせと関連する既読レコードを削除する
     *
     * @param  int $announcementId お知らせID
     * @return bool true: 削除成功、false: 対象なし or 削除失敗
     */
    public function delete(int $announcementId): bool
    {
        $announcement = Announcement::find($announcementId);
        if (!$announcement) {
            return false;
        }

        try {
            DB::transaction(function () use ($announcement, $announcementId) {
                $announcement->delete();
                $this->deleteReadsByAnnouncementId($announcementId);
            });
        } catch (\Throwable $e) {
            Log::error('お知らせの削除に失敗しました', ['announcement_id' => $announcementId, 'exception' => $e]);
            return false;
        }

        return true;
    }

    /**
     * 公開中のお知らせを未読数・既読状態付きで取得する
     *
     * @param  int $userId ユーザーID
     * @return array{ unread_count: int, announcement: Announcement[]|string }
     */
    public function getStatusRead(int $userId): array
    {
        $announcements = Announcement::published()->get();

        if ($announcements->isEmpty()) {
            return ['unread_count' => 0, 'announcement' => ''];
        }

        $announcementIds = $announcements->pluck('id')->all();
        $reads           = AnnouncementRead::query()
            ->where('user_id', $userId)
            ->whereIn('announcement_id', $announcementIds)
            ->get();
        $readIds = $reads->pluck('announcement_id')->all();

        foreach ($announcements as $announcement) {
            if (in_array($announcement->id, $readIds)) {
                $announcement->pub_status = 1;
            }
        }

        return [
            'unread_count' => count($announcementIds) - $reads->count(),
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
                $isNotPublic = $announcement->pub_start_at->gt($today)
                    || (!empty($announcement->pub_end_at) && $announcement->pub_end_at->lt($today));
                if ($isNotPublic) {
                    $this->deleteReadsByAnnouncementId($announcement->id);
                }
            });
        } catch (\Throwable $e) {
            Log::error('お知らせの保存に失敗しました', ['announcement_id' => $announcement->id, 'exception' => $e]);
            return false;
        }

        return true;
    }

    /**
     * お知らせを既読にする
     *
     * @param  int $userId         ユーザーID
     * @param  int $announcementId 既読にするお知らせID
     * @return bool true: 登録成功または既に既読、false: 登録失敗
     */
    public function markAsRead(int $userId, int $announcementId): bool
    {
        try {
            AnnouncementRead::query()->firstOrCreate([
                'user_id'         => $userId,
                'announcement_id' => $announcementId,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('お知らせの既読登録に失敗しました', ['user_id' => $userId, 'announcement_id' => $announcementId, 'exception' => $e]);
            return false;
        }
    }

    /**
     * お知らせ一覧を取得する
     *
     * @param  bool  $onlyId true の場合はIDのみ取得
     * @param  array $target  取得対象のお知らせIDの配列（空の場合は全件）
     * @return array<int, array>
     */
    public function getAnnouncements(bool $onlyId = false, array $target = []): array
    {
        $query = Announcement::query();
        if ($onlyId === true) {
            $query->select('id');
        }
        if (!empty($target)) {
            $query->whereIn('id', $target);
        }
        return $query->get()->toArray();
    }

    /**
     * お知らせIDに紐づく既読レコードを削除する
     *
     * @param  int $announcementId お知らせID
     * @return void
     */
    private function deleteReadsByAnnouncementId(int $announcementId): void
    {
        AnnouncementRead::query()
            ->where('announcement_id', $announcementId)
            ->delete();
    }
}
