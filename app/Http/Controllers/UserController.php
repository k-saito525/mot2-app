<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use App\Models\User;
use App\Models\Topic;
use App\Services\UserService;

/**
 * ユーザー情報関連のコントローラ
 */
class UserController extends Controller
{
    // ユーザー一覧画面のデフォルト表示件数
    const int SHOW_CNT_USERS = 40;

    public function __construct(private readonly UserService $userService) {}

    /**
     * ユーザー情報 - 一覧画面の表示
     *
     * @param string $page  一覧のページ番号
     * @return View
     */
    public function showList(string $page = '1'): View
    {
        $page = max(1, (int)$page);
        $users = User::orderBy('created_at', 'desc')
            ->paginate(self::SHOW_CNT_USERS, ['*'], 'page', $page);

        return view('user/index', [
            'users' => $users,
        ]);
    }

    /**
     * ユーザー情報 - 詳細画面の表示
     *
     * @param string $id  ユーザーID
     * @return View|RedirectResponse
     */
    public function showDetail(string $id): View|RedirectResponse
    {
        // IDを元にユーザー情報を取得
        $user = User::approved()->find((int)$id);
        if ($user === null) {
            return to_route('user.show.list');
        }

        /* ユーザーIDをもとにそのユーザーが作成したトピックを取得 */
        $topics = new Topic()->getTopicByUser($id);
        return view('user/show/index', [
            'user' => $user,
            'topics' => $topics,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * ユーザー情報 - 編集画面の表示
     *
     * @param string $id  ユーザーID
     * @return View|RedirectResponse
     */
    public function showEdit(string $id): View|RedirectResponse
    {
        $user = User::approved()->find((int)$id);
        if ($user === null) {
            return to_route('user.show.list');
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
                if (User::where('email', $input['email'])->exists()) {
                    session()->flash('flash_failed_email', __('users.fail.duplicate_mail'));
                    return back();
                }
            }

            // 更新対象のユーザーを取得
            $target_user = User::approved()->find((int)Arr::get($input, 'user_id'));
            if ($target_user === null) {
                /* ユーザーが取得できなければ404(基本ここは通らない想定) */
                abort(404);
            }

            // 更新実行
            $error = $this->userService->updateProfile($input);
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
