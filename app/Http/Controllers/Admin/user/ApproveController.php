<?php

namespace App\Http\Controllers\Admin\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Mail\MailApprovedUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ApproveController extends Controller
{
    // userモデルのインスタンス
    public $m_user;

    public function __construct()
    {
        $this->m_user = new User();
        return $this->m_user;
    }

    /**
     * 承認待ちユーザー - 一覧表示
     */
    public function showList()
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
     * @param int $id ユーザーID
     */
    public function showDetail(int $id)
    {
        // IDを元にユーザー情報を取得
        $unapproved_user = $this->m_user->getUnapprovedUser($id);
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

        if (!empty($unapproved_user)) {
            return view('admin/user/unapproved/detail', [
                'user' => $unapproved_user,
            ]);
        } else {
            return to_route('404');
        }
    }

    /**
     * 承認待ちユーザー - 承認処理
     * 
     * @param Request $request
     */
    public function approve(Request $request)
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
            } catch (\Exception $e) {
                // 登録失敗したら404
                return to_route('404');
            }
        } else {
            /* ユーザー情報が取得できなかった場合は承認待ちユーザー一覧に戻す */
            return to_route('admin.show.unapproved.list');
        }
    }
}
