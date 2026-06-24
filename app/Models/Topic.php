<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'topics';

    protected $fillable = [
        'title',
        'content',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    private $columns = [
        'topics.id',
        'topics.title',
        'topics.content',
        'topics.user_id',
        'topics.created_at',
        'topics.updated_at',
        'users.name',
        'users.user_icon',
        'users.user_identifier',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getTopics(int $limit = 0)
    {
        $query = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select($this->columns)
            ->orderBy('topics.created_at', 'desc');
        if (!empty($limit)) {
            $query = $query->limit($limit);
        }
        $topics = $query->get();
        foreach ($topics as $topic) {
            $topic->content = self::makeLink($topic->content ?? '');
        }
        return $topics;
    }

    public function getTopicsList(int|null $limit = null, int|null $offset = null): array
    {
        $topic_info = [];
        $query = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select($this->columns)
            ->orderBy('topics.created_at', 'desc');
        if (!empty($limit)) {
            $query = $query->limit($limit);
        }
        if (!empty($offset)) {
            $query = $query->offset($offset);
        }
        $topics = $query->get();
        foreach ($topics as $topic) {
            $topic->content = self::makeLink($topic->content ?? '');
        }
        $topic_info['topics'] = $topics->all();
        $topic_info['cnt']    = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->count();
        return $topic_info;
    }

    public function getTopicById(int|string $topic_id, bool $flg_link = true)
    {
        $topic = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select($this->columns)
            ->where('topics.id', $topic_id)
            ->first();
        if ($flg_link === true && $topic !== null && !empty($topic->content)) {
            $topic->content = self::makeLink($topic->content);
        }
        return $topic;
    }

    public function getTopicByUser(int $user_id)
    {
        $topics = static::query()
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select($this->columns)
            ->where('topics.user_id', $user_id)
            ->get();
        foreach ($topics as $topic) {
            $topic->content = self::makeLink($topic->content ?? '');
        }
        return $topics;
    }

    public static function makeLink(string $content = ''): string
    {
        if (empty($content)) {
            return '';
        }
        return mb_ereg_replace(
            "(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)",
            '<a class="content-link" href="\1\2">\1\2</a>',
            $content
        );
    }
}
