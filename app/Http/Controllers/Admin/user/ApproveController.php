<?php

namespace App\Http\Controllers\Admin\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\MailApprovedUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApproveController extends Controller
{
    public User $m_user;

    public function __construct()
    {
        $this->m_user = new User();
    }

    /**
     * 承認待ちユーザー - 一覧表示
     *
     * @return View
     */
    public function showList(): View
    {
        // 承認待ちのユーザー情報を取得
        $unapproved_users = $this->m_user->getUnapprovedUsers();

        return view('admin/user/unapproved/list', [
            'users' => $unapproved_users,
        ]);
    }

    /**
     * 承認待ちユーザー - 詳細表示
     *
     * @param string $id ユーザーID
     * @return View
     */
    public function showDetail(string $id): View
    {
        // IDを元にユーザー情報を取得
        $unapproved_user = $this->m_user->getUnapprovedUser((int)$id);
        if ($unapproved_user === null) {
            abort(404);
        }
        // 活動参加歴を表示用に調整
        $activity_list = __('iims_activity');
        if (!empty($unapproved_user->past_join)) {
            $key_past_join = explode(',', data_get($unapproved_user, 'past_join'));
            $text_past_join = [];
            foreach ($activity_list as $category => $list) {
                foreach ($key_past_join as $key) {
                    $res = '';
                    $res = Arr::get($list, $key);
                    if (!empty($res)) {
                        $text_past_join[] = $res;
                        continue;
                    }
                }
            }
            $unapproved_user->past_join = $text_past_join;
        }

        return view('admin/user/unapproved/detail', [
            'user' => $unapproved_user,
        ]);
    }

    /**
     * 承認待ちユーザー - 承認処理
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function approve(Request $request): RedirectResponse
    {
        // IDをもとにユーザー情報を取得
        $id = $request->post('id');
        $user = $this->m_user->getUnapprovedUser($id);

        if (!empty($user)) {
            try {
                // 承認ステータスを更新
                $this->m_user->approveUser($user->id);

                // ユーザーに承認完了通知を送信
                Mail::to($user->email)->send(new MailApprovedUser($user));

                // 処理が完了したら承認待ちユーザー一覧画面に遷移
                return to_route('admin.show.unapproved.list');
            } catch (\Exception) {
                // 登録失敗したら404
                abort(404);
            }
        } else {
            /* ユーザー情報が取得できなかった場合は承認待ちユーザー一覧に戻す */
            return to_route('admin.show.unapproved.list');
        }
    }
}
