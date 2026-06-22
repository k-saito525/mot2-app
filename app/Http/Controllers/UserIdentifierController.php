<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserIdentifierRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;

class UserIdentifierController extends Controller
{
    // userモデルのインスタンス格納用
    private $m_user;

    public function __construct()
    {
        $this->m_user = new User();
        return $this->m_user;
    }

    /**
     * ユーザーID設定 - 入力画面の表示
     * 
     * @param string $token usersテーブルの認証用トークン
     */
    public function showForm(string $token)
    {
        if (empty($token)) {
            /* 万が一認証トークンが無いURLだった場合はトップ画面に戻す */
            return to_route('top');
        }
        $user = $this->m_user->getUserByToken($token);
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
     */
    public function store(UserIdentifierRequest $request)
    {
        // 入力画面から渡されたユーザー情報をセッションから取得
        $user = session('user');
        // 既にユーザーIDが登録されている場合は完了画面に遷移
        $identifier = data_get($user, 'user_identifier');
        if (!empty($identifier)) {
            session()->flash('flash_failed', __('users.fail.duplicate_identifier'));
            return to_route('login.show.form');
        }

        // 入力データのバリデート
        $validated = $request->validated();
        // 入力データを取得
        $input = $request->only('user_identifier');

        // 登録実行
        try {
            $user->user_identifier = data_get($input, 'user_identifier');
            $user->save();

            // 登録成功したら完了画面に遷移
            return view('identifier/complete/index');
        } catch (\Exception $e) {
            session()->flash('flash_failed', __('users.fail.duplicate_identifier'));
            return back();
        }
    }

    /**
     * ユーザーID設定 - 完了画面の表示
     */
    public function showComplete()
    {

        return view('identifier.complete.index');
    }
}
