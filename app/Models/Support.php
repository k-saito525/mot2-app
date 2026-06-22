<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class Support extends Model
{
    use HasFactory;

    // テーブル名の定義
    protected $table = 'supports';

    /**
     * 登録や更新を許可しないカラムを設定
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * カラムの型定義(データ取得時に指定の型で取得する)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'message' => 'string',
        'user_id' => 'integer',
    ];

    // 表示用カラム
    private $columns = [
        'supports.id',
        'supports.message',
        'supports.created_at',
        'users.name as username',
    ];

    /**
     * メッセージ取得 - 一覧表示用
     */
    public function getMessages()
    {
        // 削除されていないメッセージを送信日時が新しい順に取得
        $messages = [];
        $messages = DB::table($this->table)
            ->join('users', 'supports.user_id', '=', 'users.id')
            ->select($this->columns)
            ->whereNull('supports.deleted_at')
            ->orderBy('supports.created_at', 'desc')
            ->get();

        return $messages;
    }

    /**
     * メッセージ取得 - 詳細表示用
     * 
     * @param int|string $id supportsテーブルのID
     */
    public function getMessageById(int|string $id)
    {
        // 削除されていないメッセージを送信日時が新しい順に取得
        $message = [];
        $message = DB::table($this->table)
            ->join('users', 'supports.user_id', '=', 'users.id')
            ->select($this->columns)
            ->where('supports.id', $id)
            ->whereNull('supports.deleted_at')
            ->orderBy('supports.created_at', 'desc')
            ->first();

        return $message;
    }
}
