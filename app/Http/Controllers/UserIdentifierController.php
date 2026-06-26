<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserIdentifierRequest;
use Illuminate\Support\Arr;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserIdentifierController extends Controller
{
    /**
     * ユーザーID設定 - 入力画面の表示
     *
     * @param string $token usersテーブルの認証用トークン
     * @return View|RedirectResponse
     */
    public function showForm(string $token): View|RedirectResponse
    {
        if (empty($token)) {
            /* 万が一認証トークンが無いURLだった場合はトップ画面に戻す */
            return to_route('top');
        }
        $user = User::where('verify_token', $token)->first();
        if (!$user) {
            /* 認証トークンからユーザー情報を取得できなければトップ画面に戻す */
            return to_route('top');
        } else {
            /* ユーザー情報をセッションに保存 */
            session(['user' => $user]);
        }

        return view('identifier/index', [
            'user' => $user,
        ]);
    }

    /**
     * ユーザーID設定 - 登録実行
     *
     * @return View|RedirectResponse
     */
    public function store(UserIdentifierRequest $request): View|RedirectResponse
    {
        // 入力画面から渡されたユーザー情報をセッションから取得
        $user = session('user');
        // 既にユーザーIDが登録されている場合は完了画面に遷移
        $identifier = data_get($user, 'user_identifier');
        if (!empty($identifier)) {
            session()->flash('flash_failed', __('users.fail.duplicate_identifier'));
            return to_route('login.show.form');
        }

        // 入力データを取得
        $input = $request->only('user_identifier');

        // 登録実行
        try {
            $user->user_identifier = Arr::get($input, 'user_identifier');
            $user->save();

            // 登録成功したら完了画面に遷移
            return view('identifier/complete/index');
        } catch (\Exception) {
            session()->flash('flash_failed', __('users.fail.duplicate_identifier'));
            return back();
        }
    }

    /**
     * ユーザーID設定 - 完了画面の表示
     *
     * @return View
     */
    public function showComplete(): View
    {

        return view('identifier.complete.index');
    }
}
