<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Topic;

/**
 * ユーザー情報関連のコントローラ
 */
class UserController extends Controller
{
    // ユーザー一覧画面のデフォルト表示件数
    const int SHOW_CNT_USERS = 40;

    private User $m_user;

    public function __construct()
    {
        $this->m_user = new User();
    }

    /**
     * ユーザー情報 - 一覧画面の表示
     *
     * @param string $page  一覧のページ番号
     * @return View
     */
    public function showList(string $page = '1'): View
    {
        // ページ番号
        $page = (int)$page;
        if ($page <= 0) {
            /* 不正なページ番号の場合は1ページに設定 */
            $page = 1;
        }

        // 表示件数
        $limit = self::SHOW_CNT_USERS;
        // 何件目から取得するか設定
        $offset = ($page - 1) * $limit;
        // トピック情報(新しい順)と総件数を取得
        $user_info = $this->m_user->getUsersList($limit, $offset);
        // 取得したトピック情報をトピックと総件数に分ける
        $users = [];
        $total_cnt = 0;
        if (!empty($user_info)) {
            $users = Arr::get($user_info, 'users', []);
            $total_cnt = Arr::get($user_info, 'cnt', 0);
        }

        /* ページネーション */
        // 次のページ番号
        $page_next = '';
        if ($total_cnt > (self::SHOW_CNT_USERS * $page)) {
            $page_next = $page + 1;
        }
        // 前のページ番号
        $page_previous = $page - 1;

        return view('user/index', [
            'users' => $users,
            'total_cnt' => $total_cnt,
            'page' => $page,
            'page_next' => $page_next,
            'page_previous' => $page_previous,
        ]);
    }

    /**
     * ユーザー情報 - 詳細画面の表示
     *
     * @param string $user_id  ユーザーID
     * @return View|RedirectResponse
     */
    public function showDetail(string $user_id): View|RedirectResponse
    {
        // IDを元にユーザー情報を取得
        $user = $this->m_user->getUserById((int)$user_id);
        if ($user === null) {
            return to_route('user.show.list');
        }

        // sns_links JSON をビュー用に個別プロパティへ展開
        if (!empty($user->sns_links)) {
            $sns = json_decode($user->sns_links, true);
            $user->sns_x = Arr::get($sns, 'x', '');
            $user->sns_facebook = Arr::get($sns, 'facebook', '');
            $user->sns_instagram = Arr::get($sns, 'instagram', '');
        }
        // 活動参加歴を表示用に変換 ※初期段階では表示無し
        if (!empty($user->past_join)) {
            $key_past_join = json_decode($user->past_join, true) ?? [];
            $activity_list = __('iims_activity');
            $text_past_join = [];
            foreach ($activity_list as $category => $list) {
                foreach ($key_past_join as $key) {
                    $res = Arr::get($list, $key, '');
                    if (!empty($res)) {
                        $text_past_join[] = $res;
                        continue;
                    }
                }
            }
            $user->past_join = $text_past_join;
        }

        /* ユーザーIDをもとにそのユーザーが作成したトピックを取得 */
        $topics = new Topic()->getTopicByUser($user_id);
        return view('user/show/index', [
            'user' => $user,
            'topics' => $topics,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * ユーザー情報 - 編集画面の表示
     *
     * @param string $user_id  ユーザーID
     * @return View|RedirectResponse
     */
    public function showEdit(string $user_id): View|RedirectResponse
    {
        $user = $this->m_user->getUserById((int)$user_id);
        if ($user === null) {
            return to_route('user.show.list');
        }
        // sns_links JSON をビュー用に個別プロパティへ展開
        if (!empty($user->sns_links)) {
            $sns = json_decode($user->sns_links, true);
            $user->sns_x = Arr::get($sns, 'x', '');
            $user->sns_facebook = Arr::get($sns, 'facebook', '');
            $user->sns_instagram = Arr::get($sns, 'instagram', '');
        }

        // IIMS活動参加歴
        $activity_list = __('iims_activity');

        return view('user/edit/index', [
            'user' => $user,
            'activity_list' => $activity_list,
        ]);
    }

    /**
     * ユーザー情報 - 更新実行
     *
     * @param UserRequest $request
     * @return RedirectResponse
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $input = $request->all();

        if (empty($input)) {
            /* 入力情報が無い場合 ※バリデートがあるため通常操作ではこの処理は通らない想定 */
            return back();
        } else {
            // メールアドレスの重複確認
            if (!empty($input['email'])) {
                $res = $this->m_user->checkMail($input['email']);
                if (!$res) {
                    // エラーメッセージを表示
                    session()->flash('flash_failed_email', __('users.fail.duplicate_mail'));
                    return back();
                }
            }

            // 更新対象のユーザーを取得
            $target_user = $this->m_user->getUserById((int)Arr::get($input, 'user_id'));
            if ($target_user === null) {
                /* ユーザーが取得できなければ404(基本ここは通らない想定) */
                abort(404);
            }

            // 更新実行
            $error = $this->m_user->saveUser($input);
            if (empty($error)) {
                /* エラーメッセージがなければ更新成功 */
                session()->flash('flash_success', __('users.success.updated'));
                return to_route('user.show.detail', ['id' => $input['user_id']]);
            } else {
                /* 更新失敗 */
                session()->flash('flash_failed', $error);
                return back();
            }
        }
    }
}
