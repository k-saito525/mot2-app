<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

/**
 * ログイン用コントローラ
 */
class LoginController extends Controller
{
    /*
     * ログインフォーム表示
     * ※ログインしていたらホーム画面に遷移させる
     */
    public function showForm()
    {
        return view('login/index');
    }

    /*
     * ログイン処理
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        // 入力データをバリデーション
        $tmp_credentials = $request->validated();
        // 認証条件に「削除されていないユーザー」を追加
        $credentials = [
            'email' => data_get($tmp_credentials, 'email'),
            'password' => data_get($tmp_credentials, 'password'),
            'deleted_at' => null,
        ];

        /*  管理者画面アクセス時は、管理者権限チェックを追加する */
        // 現在のURL取得
        $url = url()->current();
        if (!empty(strpos($url, '/admin'))) {
            $credentials = Arr::add($credentials, 'is_admin', 1);
        }

        /* バリデーションOKの場合 */
        // ログイン情報が正しいか確認
        if (Auth::attempt($credentials, true)) {
            /* ログイン成功 */
            // セッションを再生成(セキュリティ対策)
            $request->session()->regenerate();
            return redirect()->intended('home');
        } else {
            session()->flash('flash_failed', __('auth.failed_login'));
            return back();
        }
    }

    /*
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        // ログアウト処理
        Auth::logout();

        // 現在のセッションを削除し、再生成する(セキュリティ対策)
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // TOPにリダイレクト
        return view('logout/index');
    }
}
