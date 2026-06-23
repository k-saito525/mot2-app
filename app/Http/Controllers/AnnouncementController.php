<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AnnouncementRequest;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
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
        $today = new Carbon(date('Y-m-d'));
        foreach ($announcement_list as $key => $val) {
            if (!empty($val->pub_end_at) && new Carbon($val->pub_end_at) < $today) {
                $announcement_list[$key]->pub_status = '公開終了';
            } else {
                if (new Carbon($val->pub_start_at) <= $today) {
                    /* 公開期間内 */
                    $announcement_list[$key]->pub_status = '公開中';
                } else {
                    /* 公開期間終了 */
                    $announcement_list[$key]->pub_status = '公開前';
                }
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
            'announcement' => data_get($announcement, 0, []),
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
            'announcement' => data_get($announcement, 0, []),
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

            $m_announcements = new Announcement();
            $m_announcements = $m_announcements::find(Arr::get($post, 'announcement_id'));
            // 削除実行
            $m_announcement_read = new AnnouncementRead();
            try {
                $m_announcements->delete();
                // announce_readテーブルからも削除
                $m_announcement_read->_update(Arr::get($post, 'announcement_id'), 0);
                return to_route('admin.show.announcement.list');
            } catch (\Exception) {
                abort(404);
            }
        }

        /* 新規作成・更新 */
        // 入力データのバリデート
        $request->validated();
        $input = $request->all();

        if (!empty(Arr::get($input, 'pub-end')) && strtotime(Arr::get($input, 'pub-start')) > strtotime(Arr::get($input, 'pub-end'))) {
            session()->flash('pub-start', '日付の選択が正しくありません');
            return back();
        }
        // 公開ステータスの確認 1:公開中
        //  現在時刻を取得
        $now = strtotime('now');
        if ($now < strtotime(Arr::get($input, 'pub-start')) || $now > strtotime(Arr::get($input, 'pub-end'))) {
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
        try {
            // データベースに保存
            $m_announcements->save();
        } catch (\Exception) {
            // 登録失敗したら入力画面に戻る
            return back();
        }

        // 更新後の公開状況によってannouncement_readsテーブルを更新する
        $today = Carbon::today();
        if ($m_announcements->pub_start_at->gt($today)) {
            /* 公開前 */
            $flg = 0;
        } elseif (!empty($m_announcements->pub_end_at) && $m_announcements->pub_end_at->lt($today)) {
            /* 公開終了 */
            $flg = 0;
        } else {
            /* 公開中 */
            $flg = 1;
        }
        if (isset($flg)) {
            $m_announcement_reads = new AnnouncementRead();
            $m_announcement_reads->_update($m_announcements->id, $flg);
        }

        // 一覧画面に遷移
        return to_route('admin.show.announcement.list');
    }
}
