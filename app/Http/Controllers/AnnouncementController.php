<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(private readonly AnnouncementService $announcementService) {}

    /**
     * お知らせ - 一覧画面の表示(管理者側)
     *
     * @return View
     */
    public function showList(): View
    {
        $announcementList = Announcement::all();

        return view('admin/announcement/index', [
            'announcement_list' => $announcementList,
        ]);
    }

    /**
     * お知らせ - 詳細画面の表示(表層側)
     *
     * @param string $id  お知らせID
     * @return View|RedirectResponse
     */
    public function showDetail(string $id): View|RedirectResponse
    {
        $announcementId = (int)$id;
        // お知らせ取得
        $announcement = $this->announcementService->getAnnouncements(false, [$announcementId]);

        // 表層側で表示されたお知らせは既読にする
        $res = $this->announcementService->markAsRead(Auth::id(), $announcementId);

        if ($res === false) {
            /* DB更新失敗したらとりあえずHOME画面に戻す */
            return back();
        }

        return view('announcement/detail/index', [
            'announcement' => Arr::get($announcement, 0, []),
        ]);
    }

    /**
     * お知らせ - 新規作成画面の表示
     *
     * @return View
     */
    public function showCreate(): View
    {
        return view('admin/announcement/new/index');
    }

    /**
     * お知らせ - 編集画面の表示
     *
     * @param string $id  お知らせID
     * @return View
     */
    public function showEdit(string $id): View
    {
        // お知らせ取得
        $announcement = $this->announcementService->getAnnouncements(false, (array)$id);
        if (empty($announcement)) {
            abort(404);
        }

        return view('admin/announcement/edit/index', [
            'announcement' => Arr::get($announcement, 0, []),
        ]);
    }

    /**
     * お知らせ - 新規作成実行
     *
     * @return RedirectResponse
     */
    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $pubStart = $request->input('pub_start');
        $pubEnd   = $request->input('pub_end');

        if (!empty($pubEnd) && Carbon::parse($pubStart)->gt(Carbon::parse($pubEnd))) {
            session()->flash('pub_start', '日付の選択が正しくありません');
            return back();
        }

        $announcement          = new Announcement();
        $announcement->user_id = Auth::id();
        $this->fillAnnouncement($announcement, $request, $pubStart, $pubEnd);

        $result = $this->announcementService->saveAndSyncReads($announcement);
        if (!$result) {
            return back();
        }
        return to_route('admin.show.announcement.list');
    }

    /**
     * お知らせ - 更新実行
     *
     * @param string $id 更新するお知らせID
     * @return RedirectResponse
     */
    public function update(AnnouncementRequest $request, string $id): RedirectResponse
    {
        $announcement = Announcement::find((int)$id);
        if ($announcement === null) {
            abort(404);
        }

        $pubStart = $request->input('pub_start');
        $pubEnd   = $request->input('pub_end');

        if (!empty($pubEnd) && Carbon::parse($pubStart)->gt(Carbon::parse($pubEnd))) {
            session()->flash('pub_start', '日付の選択が正しくありません');
            return back();
        }

        $this->fillAnnouncement($announcement, $request, $pubStart, $pubEnd);

        $result = $this->announcementService->saveAndSyncReads($announcement);
        if (!$result) {
            return back();
        }
        return to_route('admin.show.announcement.list');
    }

    /**
     * お知らせ - 削除実行
     *
     * @param string $id 削除するお知らせID
     * @return RedirectResponse
     */
    public function destroy(AnnouncementRequest $request, string $id): RedirectResponse
    {
        $result = $this->announcementService->delete((int)$id);
        if (!$result) {
            abort(404);
        }
        return to_route('admin.show.announcement.list');
    }

    /**
     * リクエストの値をAnnouncementに反映する
     */
    private function fillAnnouncement(Announcement $announcement, AnnouncementRequest $request, string $pubStart, ?string $pubEnd): void
    {
        $now = Carbon::now();
        if ($now->lt(Carbon::parse($pubStart)) || (!empty($pubEnd) && $now->gt(Carbon::parse($pubEnd)))) {
            $isPublic = 0;
        } else {
            $isPublic = 1;
        }

        $announcement->title          = $request->input('announcement_title');
        $announcement->content        = $request->input('announcement_detail');
        $announcement->pub_start_at   = $pubStart;
        $announcement->publish_status = $isPublic;

        if (!empty($pubEnd)) {
            $announcement->pub_end_at = $pubEnd;
        } else {
            $announcement->pub_end_at = null;
        }
    }
}
