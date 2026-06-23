<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use App\Models\Topic;
use App\Models\Comment;
use Illuminate\View\View;

/**
 * ログイン後のTOP画面
 *  ・トピック最新5件表示
 *  ・運営への問い合わせ(メッセージ)フォーム
 */
class HomeController extends Controller
{
    // ホーム画面に表示するトピック数
    const int CNT_SHOW_TOPIC = 5;

    private Topic $m_topic;
    private Comment $m_comment;

    public function __construct()
    {
        $this->m_topic = new Topic();
        $this->m_comment = new Comment();
    }

    /**
     * ホーム画面の表示
     *
     * @return View
     */
    public function index(): View
    {
        // ログインしているユーザー
        $user_info = Auth::user();
        $user_id = $user_info->id;

        /* 最新のトピックを取得 */
        // $topics = $this->m_topic->getTopics(self::CNT_SHOW_TOPIC);
        /* ※暫定対応 最新順で6件取得して、1件はおすすめトピックとして表示 */
        $topics = $this->m_topic->getTopics(6);
        if (!$topics->isEmpty()) {
            $recc_topic = data_get($topics, 0);
            $comment_recc_topics = $this->m_comment->getCommentsByTopicID(data_get($recc_topic, 'id'));
            // 抜き出した最新の1件は削除
            Arr::except($topics, 0);
        } else {
            /* トピックが1件も存在しない場合はエラー回避のため空配列を作成 */
            $recc_topic = [];
            $comment_recc_topics = [];
        }


        return view('home/index', [
            'user_id' => $user_id,
            'user_info' => $user_info,
            'recc_topic' => $recc_topic,
            'comment_recc_topics' => $comment_recc_topics,
            'topics' => $topics,
        ]);
    }
}
