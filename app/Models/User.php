<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailChangeEmail;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    // テーブル名の定義
    protected $table = 'users';

    /**
     * 登録や更新を許可しないカラムを設定
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * カラムの型定義(データ取得時に指定の型で取得する)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'verify_token' => 'string',
        'is_admin' => 'integer',
        'is_approved' => 'integer',
    ];

    /**
     * ユーザー情報一括取得
     * 
     * @param bool $except true→特定のユーザー(自分)を取得対象から外す
     * @param int  $id     取得対象から外すユーザーのID
     */
    public function getAllUsers(bool $except = false, int $id = null)
    {
        // 削除されていない承認済みのユーザーをid順に取得
        $query = $this->where('is_approved', 1)
            ->whereNull('deleted_at');
        if ($except) {
            /* 特定のユーザーを取得対象から外す場合 */
            $query = $query->where('id', '!=', $id);
        }
        $users = $query->orderBy('id', 'asc')
            ->get();

        return $users;
    }

    /**
     * ユーザー一覧に表示する情報を取得
     * 
     * @param int|null $limit   取得件数
     * @param int|null $offset  取得開始レコード数
     */
    public function getUsersList(int|null $limit = null, int|null $offset = null): array
    {
        /* トピック取得 */
        $user_info = [];
        $query = DB::table($this->table)
            ->whereNull('users.deleted_at')
            ->orderBy('users.created_at', 'desc');
        if (!empty($limit)) {
            /* 取得件数の設定 */
            $query = $query->limit($limit);
        }
        if (!empty($offset)) {
            /* 何件目から取得するか設定 */
            $query = $query->offset($offset);
        }
        $user_info['users'] = $query->get()->toArray();

        /* トータル件数取得 */
        $user_info['cnt'] = DB::table($this->table)
            ->whereNull('users.deleted_at')
            ->count();

        return $user_info;
    }

    /**
     * IDからユーザーの情報を取得
     * 
     * @param int $id  ユーザーID
     * @return $user
     */
    public function getUserById(int $id)
    {
        // IDを元に承認済みのユーザー情報を取得
        $user = DB::table('users')
            ->where('id', $id)
            ->where('is_approved', 1)
            ->whereNull('deleted_at')
            ->first();

        return $user;
    }

    /**
     * メールアドレスからユーザーの情報を取得
     * 
     * @param string $email  メールアドレス
     * @return $user
     */
    public function getUserByEmail(string $email)
    {
        // メールアドレスを元に承認済みのユーザー情報を取得
        $user = $this->where([
            ['email', '=', $email],
            ['is_approved', '=', 1],
        ])
            ->whereNull('deleted_at')
            ->first();

        return $user;
    }

    /**
     * メールアドレスの重複チェック
     * 活きユーザーの中で同じメールアドレスが登録されていたらfalseを返す
     * 
     * @param string $mail メールアドレス
     * @return bool  true:重複なし、false:重複あり
     */
    public function checkMail(string $mail = ''): bool
    {
        $res = false;
        $ret = DB::table('users')
            ->where('email', $mail)
            ->whereNull('deleted_at')
            ->first();

        if (empty($ret)) {
            /* ユーザーIDが別のユーザーに登録されていなければtrueを返却 */
            $res = true;
        }

        return $res;
    }

    /**
     * ユーザーID(user_identifier)の重複チェック
     * 指定のユーザーIDが別のユーザーに登録されていたらfalseを返す
     * 
     * @param int|string $user_id usersテーブルのid
     * @param string $user_identifier usersテーブルのuser_identifier
     * @return bool
     */
    public function checkUserIdentifier(int|string $user_id, string $user_identifier)
    {
        $res = false;
        $ret = DB::table('users')
            ->whereNot('id', $user_id)
            ->where('user_identifier', $user_identifier)
            ->whereNull('deleted_at')
            ->first();

        if (empty($ret)) {
            /* ユーザーIDが別のユーザーに登録されていなければtrueを返却 */
            $res = true;
        }

        return $res;
    }

    /**
     * 承認待ちユーザーを全て取得
     */
    public function getUnapprovedUsers()
    {
        // 承認待ちユーザーを取得
        $unapproved_users = $this->where('is_approved', 0)
            ->whereNull('deleted_at')
            ->get();

        return $unapproved_users;
    }

    /**
     * ユーザーの承認処理
     * 
     * @param int $id  承認するユーザーのID
     * @return void
     */
    public function approveUser($id)
    {
        // 承認待ちユーザーを取得
        $user = $this->where('id', $id)
            ->first();
        // 承認ステータスを1(承認済)に設定
        $user->is_approved = 1;
        // 変更を保存
        $user->save();
    }

    /**
     * 承認待ちユーザーの情報を取得
     * 
     * @param int $id  確認するユーザーのID
     */
    public function getUnapprovedUser(int $id)
    {
        $res = null;
        // IDと承認ステータスを元にユーザー情報を取得
        $res = $this->where([
            ['id', '=', $id],
            ['is_approved', '=', 0],
        ])->whereNull('deleted_at')
            ->first();

        return $res;
    }

    /**
     * 認証用トークンからユーザー情報を取得する
     * 
     * @param string $token  認証用トークン
     */
    public function getUserByToken(string $token)
    {
        $user = $this->where([
            'verify_token' => $token,
        ])->whereNull('deleted_at')
            ->first();

        if (!empty($user)) {
            // 認証トークンをデコードしてメールアドレスとの一致確認
            $decoded_token = base64_decode($token);
            if ($decoded_token === $user->email) {
                /* 一致したらユーザー情報を返す */
                return $user;
            }
        }
        // ユーザー情報が取得できないかメールアドレスと一致しない場合はfalseを返す
        return false;
    }

    /**
     * パスワード再設定キーからユーザー情報を取得
     * 
     * @param string $reset_token パスワード再設定キー
     */
    public function getUserByResetPasswordAccessKey(string $reset_token)
    {
        $user = $this->where('reset_password_access_key', $reset_token)
            ->whereNull('deleted_at')
            ->first();

        return $user;
    }

    /**
     * ユーザー情報更新
     * 
     * @param array $input  ユーザー情報編集画面で入力された内容
     * @return string       エラーがある場合はエラーメッセージを返す
     */
    public function saveUser(array $input): string
    {

        // 返却用のエラーメッセージ格納用
        $error = '';
        // 更新するユーザーを取得
        $user = $this->find($input['user_id']);
        // メールアドレスが変更された場合に通知をするかどうかのフラグ
        $changed_email = false;

        /* 更新内容をセット */
        // 名前
        if (!empty($input['name'])) {
            $user->name = data_get($input, 'name');
        }

        // ユーザーID
        if (!empty($input['user_identifier'])) {
            // 指定されたユーザーIDが別のユーザーに登録されていないかをチェック
            if (!$this->checkUserIdentifier($user->id, $input['user_identifier'])) {
                /* 別のユーザーに登録されている場合はエラーメッセージを表示 */
                $error = __('users.fail.duplicate_identifier');
                return $error;
            }
            $user->user_identifier = data_get($input, 'user_identifier');
        }

        // メールアドレス
        if (!empty($input['email'])) {
            // メール通知で使用するため旧アドレスを保持しておく
            $old_email = $user->email;
            $user->email = data_get($input, 'email');
            $changed_email = true;
        }

        // SNSアカウントURL → sns_links JSON {"x": "...", "facebook": "...", "instagram": "..."}
        $sns_links = json_decode($user->sns_links ?? '{}', true) ?? [];
        if (!empty($input['sns_x'])) {
            $sns_links['x'] = data_get($input, 'sns_x');
        }
        if (!empty($input['sns_facebook'])) {
            $sns_links['facebook'] = data_get($input, 'sns_facebook');
        }
        if (!empty($input['sns_instagram'])) {
            $sns_links['instagram'] = data_get($input, 'sns_instagram');
        }
        $user->sns_links = json_encode($sns_links);

        // 自己紹介テキスト
        if (!empty($input['introduction_text'])) {
            $user->introduction_text = data_get($input, 'introduction_text');
        }

        // 活動参加歴 (past_join は JSON 型カラム)
        if (!empty($input['past-join'])) {
            $user->past_join = json_encode($input['past-join']);
        }

        /* 
         * 画像保存場所
         * アイコン：/storage/app/public/icon
         * カバー画像：/storage/app/public/cover
         */
        // ユーザーアイコン(プロフィール画像)
        if (!empty($input['user_icon'])) {
            if (!empty($user->user_icon)) {
                /* 現在の画像を削除 */
                $old_icon_path = data_get($user, 'user_icon');
                Storage::disk('public')->delete('icon/', $old_icon_path);
            }
            // 新しい画像を保存 (DB保存するのはファイル名のみ)
            $path_icon = data_get($input, 'user_icon')->store('icon');
            $user->user_icon = str_replace('public/icon/', '', $path_icon);
        }

        // カバー画像
        if (!empty($input['user_cover_image'])) {
            if (!empty($user->user_cover_image)) {
                /* 現在の画像を削除 */
                $old_cover_path = data_get($user, 'user_cover_image');
                Storage::disk('public')->delete('cover/', $old_cover_path);
            }
            // 新しい画像を保存 (DB保存するのはファイル名のみ)
            $path_cover = data_get($input, 'user_cover_image')->store('cover');
            $user->user_cover_image = str_replace('public/cover/', '', $path_cover);
        }

        try {
            // データベースに保存
            $user->save();
            // メールアドレスが変更された場合は新アドレス宛にメール通知
            if ($changed_email === true) {
                Mail::to($user->email)->send(new MailChangeEmail($user, $old_email));
            }
        } catch (\Exception $e) {
            // 登録失敗
            $error = __('users.fail.failed_update');
        }

        // 正常に更新成功なら、空文字を返す
        return $error;
    }

    /**
     * 活動参加歴キーを表示用テキストに変換
     * 
     * @param string $key 活動参加歴のキー
     * @return string $ret
     */
    public function convertPastJoinToText(string $key): string
    {
        $arr_ret = [];
        // 活動参加歴情報を取得
        $activity_list = __('iims_activity');
        // CSV → 配列に変換
        $arr_past_join = explode(',', $key);

        foreach ($activity_list as $category => $list) {
            foreach ($arr_past_join as $key) {
                $view_text = '';
                $view_text = Arr::get($list, $key);
                if (!empty($view_text)) {
                    $arr_ret[] = $view_text;
                    continue;
                }
            }
        }
        $ret = implode(',', $arr_ret);

        return $ret;
    }
}
