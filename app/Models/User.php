<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use App\Mail\MailChangeEmail;

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

    public function getAllUsers(bool $except = false, int $id = null)
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
        $query = DB::table($this->table)
            ->whereNull('users.deleted_at')
            ->orderBy('users.created_at', 'desc');
        if (!empty($limit)) {
            $query = $query->limit($limit);
        }
        if (!empty($offset)) {
            $query = $query->offset($offset);
        }
        $user_info['users'] = $query->get()->toArray();
        $user_info['cnt'] = DB::table($this->table)
            ->whereNull('users.deleted_at')
            ->count();
        return $user_info;
    }

    public function getUserById(int $id)
    {
        return DB::table('users')
            ->where('id', $id)
            ->where('is_approved', 1)
            ->whereNull('deleted_at')
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
        $ret = DB::table('users')
            ->where('email', $mail)
            ->whereNull('deleted_at')
            ->first();
        return empty($ret);
    }

    public function checkUserIdentifier(int|string $user_id, string $user_identifier): bool
    {
        $ret = DB::table('users')
            ->whereNot('id', $user_id)
            ->where('user_identifier', $user_identifier)
            ->whereNull('deleted_at')
            ->first();
        return empty($ret);
    }

    public function getUnapprovedUsers()
    {
        return $this->where('is_approved', 0)->get();
    }

    public function approveUser(int $id): void
    {
        $user = $this->where('id', $id)->first();
        $user->is_approved = 1;
        $user->save();
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

    public function saveUser(array $input): string
    {
        $error = '';
        $user = $this->find($input['user_id']);
        $changed_email = false;

        if (!empty($input['name'])) {
            $user->name = data_get($input, 'name');
        }

        if (!empty($input['user_identifier'])) {
            if (!$this->checkUserIdentifier($user->id, $input['user_identifier'])) {
                return __('users.fail.duplicate_identifier');
            }
            $user->user_identifier = data_get($input, 'user_identifier');
        }

        if (!empty($input['email'])) {
            $old_email = $user->email;
            $user->email = data_get($input, 'email');
            $changed_email = true;
        }

        // sns_links は 'array' cast により自動的に配列で取得・保存される
        $sns_links = $user->sns_links ?? [];
        if (!empty($input['sns_x'])) {
            $sns_links['x'] = data_get($input, 'sns_x');
        }
        if (!empty($input['sns_facebook'])) {
            $sns_links['facebook'] = data_get($input, 'sns_facebook');
        }
        if (!empty($input['sns_instagram'])) {
            $sns_links['instagram'] = data_get($input, 'sns_instagram');
        }
        $user->sns_links = $sns_links;

        if (!empty($input['introduction_text'])) {
            $user->introduction_text = data_get($input, 'introduction_text');
        }

        // past_join は 'array' cast により自動的に配列で取得・保存される
        if (!empty($input['past-join'])) {
            $user->past_join = $input['past-join'];
        }

        if (!empty($input['user_icon'])) {
            if (!empty($user->user_icon)) {
                Storage::disk('public')->delete('icon/', data_get($user, 'user_icon'));
            }
            $path_icon = data_get($input, 'user_icon')->store('icon');
            $user->user_icon = str_replace('public/icon/', '', $path_icon);
        }

        if (!empty($input['user_cover_image'])) {
            if (!empty($user->user_cover_image)) {
                Storage::disk('public')->delete('cover/', data_get($user, 'user_cover_image'));
            }
            $path_cover = data_get($input, 'user_cover_image')->store('cover');
            $user->user_cover_image = str_replace('public/cover/', '', $path_cover);
        }

        try {
            $user->save();
            if ($changed_email === true) {
                Mail::to($user->email)->send(new MailChangeEmail($user, $old_email));
            }
        } catch (\Exception) {
            $error = __('users.fail.failed_update');
        }

        return $error;
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
