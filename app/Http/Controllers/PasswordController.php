<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordNewRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\PasswordResetMailCheckRequest;
use App\Http\Requests\PasswordResetStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailPasswordResetMailCheck;
use Carbon\Carbon;
use App\Models\User;

/**
 * パスワード関連のコントローラ
 */
class PasswordController extends Controller
{

    // userモデルのインスタンス格納用
    private $m_user;

    public function __construct()
    {
        $this->m_user = new User();
        return $this->m_user;
    }

    /**
     * 新規パスワード登録 - 入力画面の表示
     * 
     * @param string $token usersテーブルの認証用トークン
     */
    public function showFormNew(string $token)
    {
        if (empty($token)) {
            /* 万が一認証トークンが無いURLだった場合はトップ画面に戻す */
            return to_route('top');
        }
        // 認証トークンからユーザー情報を取得できなければトップ画面に戻す
        $user = $this->m_user->getUserByToken($token);
        if (!$user) {
            return to_route('top');
        }

        if (!empty($user->password)) {
            /* 既にパスワード登録を完了している場合 */

            if (!empty($user->user_identifier)) {
                /* ユーザーIDの登録まで完了している場合はHOME画面に飛ばす */
                session()->flash('complete_regist', __('users.fail.complete_regist'));
                return to_route('login.show.form');
            } else {
                /* ユーザーID未登録の場合は入力画面に飛ばす */
                return to_route('identifier.show.form', ['token' => $user->verify_token]);
            }
        }

        // セッションにユーザー情報をセット
        if (session()->has('user_data')) {
            /* すでにセッションにユーザー情報がある場合は削除してから保存する */
            session()->forget('user_data');
        }
        session()->put(['user_data' => $user]);

        return view('password/new/index', [
            'user' => $user,
        ]);
    }

    /**
     * 新規パスワード登録 - 登録実行
     */
    public function storeNew(PasswordNewRequest $request)
    {
        // 入力データのバリデート
        $validated = $request->validated();
        // 入力データを取得
        $input = $request->only([
            'password',
            'password_confirmation',
        ]);
        // セッションから該当ユーザーの情報を取得
        $user = session()->get('user_data');

        // 登録実行
        try {
            // パスワードはモデル側でキャストするため、ここではHashは使用しない
            $user->password = $input['password'];
            $user->save();

            // 登録成功したらユーザーID設定画面に遷移
            return to_route('identifier.show.form', ['token' => $user->verify_token]);
        } catch (\Exception $e) {
            // 登録失敗したら再度入力フォームに戻してやり直させる
            session()->flash('flash_failed', __('passwords.failed_regist_reset'));
            return back();
        }
    }

    /**
     * パスワードリセット(非ログイン時) - メールアドレス入力画面の表示
     * 
     */
    public function showMailFormReset()
    {
        return view('password/mail-check/index');
    }

    /**
     * パスワードリセット(非ログイン時) - メール送信
     */
    public function resetSendMail(PasswordResetMailCheckRequest $request)
    {
        // 入力データのバリデート
        $validated = $request->validated();
        // 入力データを取得
        $email = $request->input('email');

        // 入力されたメールアドレスからユーザー情報を特定
        $user = $this->m_user->getUserByEmail($email);

        if (empty($user)) {
            // 入力されたアドレスで会員情報が取得できない場合、メールは送信せずに完了画面を表示する
            return to_route('password.reset.show.send');
        } else {
            // アクセスキーの生成
            $hashed_id = hash('sha256', $user->id);
            $user->reset_password_access_key = uniqid(rand(), $hashed_id);
            // アクセスキーの有効期限は現在時刻から24時間に設定
            $now = Carbon::now();
            $user->reset_password_expire_at = $now->addHours(24)->toDateTimeString();

            try {
                // 保存実行
                $user->save();

                // メール送信
                Mail::to($user->email)->send(new MailPasswordResetMailCheck($user));
                // 送信完了画面に遷移
                return to_route('password.reset.show.send');
            } catch (\Exception $e) {
                // 処理に失敗したらエラーメッセージを表示
                session()->flash('flash_failed', __('passwords.failed_send'));
                return back();
            }
        }
    }

    /**
     * パスワードリセット(非ログイン時) - 確認用メール送信完了画面の表示
     */
    public function showSendMailReset()
    {
        return view('password/mail-send/index');
    }

    /**
     * パスワードリセット(非ログイン時) - 確認用メール送信完了画面の表示
     */
    public function showPasswordFormReset(Request $request)
    {
        // 署名付きURLではない場合
        if (!$request->hasValidSignature()) {
            abort(403, __('passwords.expired'));
        }

        // 再設定キーと有効期限をセッションに保存
        $request->session()->put([
            'reset_token' => $request->reset_token,
            'expired_at' => $request->expires,
        ]);

        return view('password/reset/index');
    }

    /**
     * パスワードリセット(非ログイン時) - 確認用メール送信完了画面の表示
     */
    public function storeReset(PasswordResetStoreRequest $request)
    {
        // 入力データのバリデート
        $validated = $request->validated();
        // 入力データを取得
        $password = $request->input('password');
        // セッションから再設定キーと有効期限を取得
        $reset_token = $request->session()->get('reset_token');
        $expired_at = $request->session()->get('expired_at');

        // 入力されたメールアドレスからユーザー情報を特定
        $user = $this->m_user->getUserByResetPasswordAccessKey($reset_token);

        if (empty($user)) {
            // ユーザー情報が間違っている場合は404にしておく
            return to_route('404');
        } else {

            $user->password = Hash::make($password);

            try {
                // 保存実行
                $user->save();
                // 送信完了画面に遷移
                return to_route('password.reset.show.complete');
            } catch (\Exception $e) {
                // 処理に失敗したらエラーメッセージを表示
                session()->flash('flash_failed', __('passwords.failed_send'));
                return back();
            }
        }
    }

    /**
     * パスワードリセット(非ログイン時) - 確認用メール送信完了画面の表示
     */
    public function showCompleteReset()
    {
        return view('password/complete/index');
    }
}
