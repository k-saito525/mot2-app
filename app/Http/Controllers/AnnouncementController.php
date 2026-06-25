<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AnnouncementRequest;
use App\Services\AnnouncementService;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Models\Announcement;
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
        // お知らせ取得
        $m_announcement = new Announcement();
        $announcement_list = $m_announcement->getAnnouncements();

        // 今日の日付
        $today = Carbon::today();
        foreach ($announcement_list as $key => $val) {
            if (!empty($val['pub_end_at']) && Carbon::parse($val['pub_end_at'])->lt($today)) {
                $announcement_list[$key]['pub_status'] = '公開終了';
            } elseif (Carbon::parse($val['pub_start_at'])->lte($today)) {
                /* 公開期間内 */
                $announcement_list[$key]['pub_status'] = '公開中';
            } else {
                /* 公開前 */
                $announcement_list[$key]['pub_status'] = '公開前';
            }
        }
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
        $res = $m_announcement_read->storeReadStatus($announcement_id);

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
        // 作成者
        $user_id = Auth::id();

        return view('admin/announcement/new/index', [
            'user_id' => $user_id,
        ]);
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
     * お知らせ - 保存
     *
     * @return RedirectResponse
     */
    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $post = $request->post();
        if (isset($post['delete'])) {
            /* 削除 */
            $announcement_id = (int) Arr::get($post, 'announcement_id');
            $result = (new AnnouncementService())->delete($announcement_id);
            if (!$result) {
                abort(404);
            }
            return to_route('admin.show.announcement.list');
        }

        /* 新規作成・更新 */
        $input = $request->all();

        if (!empty(Arr::get($input, 'pub-end')) && Carbon::parse(Arr::get($input, 'pub-start'))->gt(Carbon::parse(Arr::get($input, 'pub-end')))) {
            session()->flash('pub-start', '日付の選択が正しくありません');
            return back();
        }
        // 公開ステータスの確認 1:公開中
        //  現在時刻を取得
        $now = Carbon::now();
        if ($now->lt(Carbon::parse(Arr::get($input, 'pub-start'))) || $now->gt(Carbon::parse(Arr::get($input, 'pub-end')))) {
            $flg_public = 0;
        } else {
            $flg_public = 1;
        }

        $m_announcements = new Announcement();
        if (!empty(Arr::get($input, 'announcement_id'))) {
            /* 更新の場合は更新対象のお知らせを取得 */
            $m_announcements = $m_announcements::find(Arr::get($input, 'announcement_id'));
        } else {
            /* 新規作成時のみ作成者のIDを保存 */
            $m_announcements->user_id = Arr::get($input, 'user_id');
        }
        $m_announcements->title = Arr::get($input, 'announcement-title');
        $m_announcements->content = Arr::get($input, 'announcement-detail');
        $m_announcements->pub_start_at = Arr::get($input, 'pub-start');
        $m_announcements->pub_end_at = Arr::get($input, 'pub-end');
        $m_announcements->publish_status = $flg_public;

        // 登録実行
        $result = (new AnnouncementService())->saveAndSyncReads($m_announcements);
        if (!$result) {
            return back();
        }

        // 一覧画面に遷移
        return to_route('admin.show.announcement.list');
    }
}
