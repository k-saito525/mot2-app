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
        $topics = Topic::withAuthor()->where('user_id', (int)$id)->get();
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
        $activityList = __('iims_activity');

        return view('user/edit/index', [
            'user' => $user,
            'activity_list' => $activityList,
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
            return back();
        }

        $targetUser = User::approved()->find((int)Arr::get($input, 'user_id'));
        if ($targetUser === null) {
            abort(404);
        }

        $email = Arr::get($input, 'email');
        if (!empty($email)) {
            $duplicate = User::where('email', $email)
                ->where('id', '!=', $targetUser->id)
                ->exists();
            if ($duplicate) {
                session()->flash('flash_failed_email', __('users.fail.duplicate_mail'));
                return back();
            }
        }

        $error = $this->userService->updateProfile($input, $targetUser);
        if (empty($error)) {
            session()->flash('flash_success', __('users.success.updated'));
            return to_route('user.show.detail', ['id' => $input['user_id']]);
        }
        session()->flash('flash_failed', $error);
        return back();
    }
}
