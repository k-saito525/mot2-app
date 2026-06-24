<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
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

    public function getAllUsers(bool $except = false, ?int $id = null)
    {
        $query = $this->where('is_approved', 1);
        if ($except) {
            $query = $query->where('id', '!=', $id);
        }
        return $query->orderBy('id', 'asc')->get();
    }

    public function getUsersList(int|null $limit = null, int|null $offset = null): array
    {
        $user_info = [];
        $query = static::query()->orderBy('created_at', 'desc');
        if (!empty($limit)) {
            $query = $query->limit($limit);
        }
        if (!empty($offset)) {
            $query = $query->offset($offset);
        }
        $user_info['users'] = $query->get()->all();
        $user_info['cnt']   = static::query()->count();
        return $user_info;
    }

    public function getUserById(int $id)
    {
        return static::where('id', $id)
            ->where('is_approved', 1)
            ->first();
    }

    public function getUserByEmail(string $email)
    {
        return $this->where([
            ['email', '=', $email],
            ['is_approved', '=', 1],
        ])->first();
    }

    public function checkMail(string $mail = ''): bool
    {
        return !static::where('email', $mail)->exists();
    }

    public function getUnapprovedUsers()
    {
        return $this->where('is_approved', 0)->get();
    }

    public function getUnapprovedUser(int $id)
    {
        return $this->where([
            ['id', '=', $id],
            ['is_approved', '=', 0],
        ])->first();
    }

    public function getUserByToken(string $token)
    {
        return $this->where(['verify_token' => $token])->first() ?? false;
    }

    public function getUserByResetPasswordAccessKey(string $reset_token)
    {
        return $this->where('reset_password_access_key', $reset_token)->first();
    }

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
