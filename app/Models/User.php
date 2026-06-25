<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'nationality',
        'introduction_text',
        'past_join',
        'user_identifier',
        'user_icon',
        'user_cover_image',
        'sns_links',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'password'                => 'hashed',
            'is_admin'                => 'boolean',
            'is_approved'             => 'boolean',
            'reset_password_expire_at' => 'datetime',
            'sns_links'               => 'array',
            'past_join'               => 'array',
        ];
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function supports(): HasMany
    {
        return $this->hasMany(Support::class);
    }

    /**
     * ユーザー一覧と総件数を取得する
     *
     * @param  ?int $limit  取得件数
     * @param  ?int $offset 取得開始位置
     * @return array{ users: array, cnt: int }
     */
    public function getUsersList(?int $limit = null, ?int $offset = null): array
    {
        $user_info = [];
        $query = static::query()->orderBy('created_at', 'desc');
        if ($limit !== null) {
            $query = $query->limit($limit);
        }
        if ($offset !== null) {
            $query = $query->offset($offset);
        }
        $user_info['users'] = $query->get()->all();
        $user_info['cnt']   = static::query()->count();
        return $user_info;
    }

    /**
     * IDを指定して承認済みユーザーを1件取得する
     *
     * @param  int $id ユーザーID
     * @return ?static null: 対象ユーザーなし
     */
    public function getUserById(int $id): ?static
    {
        return static::where('id', $id)
            ->where('is_approved', 1)
            ->first();
    }

    /**
     * メールアドレスを指定して承認済みユーザーを1件取得する
     *
     * @param  string $email メールアドレス
     * @return ?static null: 対象ユーザーなし
     */
    public function getUserByEmail(string $email): ?static
    {
        return $this->where('email', $email)
            ->where('is_approved', 1)
            ->first();
    }

    /**
     * メールアドレスが未登録かチェックする
     *
     * @param  string $mail メールアドレス
     * @return bool true: 未登録、false: 登録済み
     */
    public function checkMail(string $mail = ''): bool
    {
        return !static::where('email', $mail)->exists();
    }

    /**
     * 未承認ユーザー一覧を取得する
     *
     * @return Collection<int, static>
     */
    public function getUnapprovedUsers(): Collection
    {
        return $this->where('is_approved', 0)->get();
    }

    /**
     * IDを指定して未承認ユーザーを1件取得する
     *
     * @param  int $id ユーザーID
     * @return ?static null: 対象ユーザーなし
     */
    public function getUnapprovedUser(int $id): ?static
    {
        return $this->where('id', $id)
            ->where('is_approved', 0)
            ->first();
    }

    /**
     * メール認証トークンを指定してユーザーを1件取得する
     *
     * @param  string $token 認証トークン
     * @return ?static null: 対象ユーザーなし
     */
    public function getUserByToken(string $token): ?static
    {
        return $this->where('verify_token', $token)->first();
    }

    /**
     * パスワードリセット用アクセスキーを指定してユーザーを1件取得する
     *
     * @param  string $reset_token パスワードリセット用アクセスキー
     * @return ?static null: 対象ユーザーなし
     */
    public function getUserByResetPasswordAccessKey(string $reset_token): ?static
    {
        return $this->where('reset_password_access_key', $reset_token)->first();
    }

    /**
     * 参加歴キーを表示用テキストに変換する
     *
     * @param  string $key カンマ区切りの参加歴キー
     * @return string カンマ区切りの表示用テキスト
     */
    public function convertPastJoinToText(string $key): string
    {
        $arr_ret = [];
        $activity_list = __('iims_activity');
        $arr_past_join = explode(',', $key);
        foreach ($activity_list as $category => $list) {
            foreach ($arr_past_join as $join_key) {
                $view_text = Arr::get($list, $join_key);
                if (!empty($view_text)) {
                    $arr_ret[] = $view_text;
                }
            }
        }
        return implode(',', $arr_ret);
    }
}
