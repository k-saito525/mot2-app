<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Support extends Model
{
    use HasFactory, SoftDeletes;

    // テーブル名の定義
    protected $table = 'supports';

    protected $fillable = [
        'message',
        'user_id',
    ];

    /**
     * カラムの型定義(データ取得時に指定の型で取得する)
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    // 表示用カラム
    private $columns = [
        'supports.id',
        'supports.message',
        'supports.created_at',
        'users.name as username',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * メッセージ取得 - 一覧表示用
     */
    public function getMessages()
    {
        // 削除されていないメッセージを送信日時が新しい順に取得
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
