<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementRead extends Model
{
    use HasFactory;

    // テーブル名の定義
    protected $table = 'announcement_reads';

    // 複合主キー (user_id + announcement_id) のため id カラムは存在しない
    // Eloquent は複合主キー非対応のため find() は使用不可
    protected $primaryKey = null;
    public $incrementing = false;

    // created_at のみ存在するためupdated_atは無効化
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'announcement_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }
}
