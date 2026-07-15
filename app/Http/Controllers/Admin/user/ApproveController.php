<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Arr;

class ApproveController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    /**
     * 承認待ちユーザー - 一覧表示
     *
     * @return View
     */
    public function showList(): View
    {
        // 承認待ちのユーザー情報を取得
        $unapprovedUsers = User::unapproved()->get();

        return view('admin/user/unapproved/list', [
            'users' => $unapprovedUsers,
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
        $unapprovedUser = User::unapproved()->find((int)$id);
        if ($unapprovedUser === null) {
            abort(404);
        }
        // 活動参加歴を表示用に調整
        $activityList = __('iims_activity');
        if (!empty($unapprovedUser->past_join)) {
            $textPastJoin = [];
            foreach ($activityList as $list) {
                foreach ($unapprovedUser->past_join as $key) {
                    $res = Arr::get($list, $key);
                    if (!empty($res)) {
                        $textPastJoin[] = $res;
                    }
                }
            }
            $unapprovedUser->past_join = $textPastJoin;
        }

        return view('admin/user/unapproved/detail', [
            'user' => $unapprovedUser,
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
        $id = (int) $request->post('id');
        $user = User::unapproved()->find($id);

        if ($user === null) {
            /* ユーザー情報が取得できなかった場合は承認待ちユーザー一覧に戻す */
            return to_route('admin.show.unapproved.list');
        }

        try {
            // 承認ステータスを更新(承認完了通知の送信も内部で行う)
            $this->userService->approve($user->id);

            // 処理が完了したら承認待ちユーザー一覧画面に遷移
            return to_route('admin.show.unapproved.list');
        } catch (\Exception $e) {
            Log::error('ユーザー承認処理に失敗しました', ['user_id' => $user->id, 'exception' => $e]);
            // 登録失敗したら404
            abort(404);
        }
    }
}
