<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Services\AnnouncementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * お知らせ - 一覧画面の表示(管理者側)
     *
     * @return View
     */
    public function showList(): View
    {
        $announcement_list = Announcement::all();

        return view('admin/announcement/index', [
            'announcement_list' => $announcement_list,
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
        $announcement_id = (int)$id;
        // お知らせ取得
        $m_announcement = new Announcement();
        $announcement = $m_announcement->getAnnouncements(false, array($announcement_id));

        // 表層側で表示されたお知らせは既読にする
        $m_announcement_read = new AnnouncementRead();
        $res = $m_announcement_read->storeReadStatus(Auth::id(), $announcement_id);

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
        $m_announcement = new Announcement();
        $announcement = $m_announcement->getAnnouncements(false, (array)$id);
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
        $pub_start = $request->input('pub-start');
        $pub_end   = $request->input('pub-end');

        if (!empty($pub_end) && Carbon::parse($pub_start)->gt(Carbon::parse($pub_end))) {
            session()->flash('pub-start', '日付の選択が正しくありません');
            return back();
        }

        $announcement          = new Announcement();
        $announcement->user_id = Auth::id();
        $this->fillAnnouncement($announcement, $request, $pub_start, $pub_end);

        $result = (new AnnouncementService())->saveAndSyncReads($announcement);
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

        $pub_start = $request->input('pub-start');
        $pub_end   = $request->input('pub-end');

        if (!empty($pub_end) && Carbon::parse($pub_start)->gt(Carbon::parse($pub_end))) {
            session()->flash('pub-start', '日付の選択が正しくありません');
            return back();
        }

        $this->fillAnnouncement($announcement, $request, $pub_start, $pub_end);

        $result = (new AnnouncementService())->saveAndSyncReads($announcement);
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
        $result = (new AnnouncementService())->delete((int)$id);
        if (!$result) {
            abort(404);
        }
        return to_route('admin.show.announcement.list');
    }

    /**
     * リクエストの値をAnnouncementに反映する
     */
    private function fillAnnouncement(Announcement $announcement, AnnouncementRequest $request, string $pub_start, ?string $pub_end): void
    {
        $now = Carbon::now();
        if ($now->lt(Carbon::parse($pub_start)) || (!empty($pub_end) && $now->gt(Carbon::parse($pub_end)))) {
            $flg_public = 0;
        } else {
            $flg_public = 1;
        }

        $announcement->title          = $request->input('announcement-title');
        $announcement->content        = $request->input('announcement-detail');
        $announcement->pub_start_at   = $pub_start;
        $announcement->publish_status = $flg_public;

        if (!empty($pub_end)) {
            $announcement->pub_end_at = $pub_end;
        } else {
            $announcement->pub_end_at = null;
        }
    }
}
