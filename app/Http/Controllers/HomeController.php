<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Topic;
use App\Models\Comment;
use App\Models\Support;
use App\Models\Announcement;

/**
 * ログイン後のTOP画面
 *  ・トピック最新5件表示
 *  ・運営への問い合わせ(メッセージ)フォーム
 */
class HomeController extends Controller
{
    // ホーム画面に表示するトピック数
    const CNT_SHOW_TOPIC = 5;
    // userモデルのインスタンス
    private $m_user;
    // topicモデルのインスタンス
    private $m_topic;
    // commentモデルのインスタンス
    private $m_comment;
    // supportモデルのインスタンス
    private $m_support;

    public function __construct()
    {
        // // userモデルのインスタンス生成
        $this->m_user = new User();
        // topicモデルのインスタンス生成
        $this->m_topic = new Topic();
        // commentモデルのインスタンス生成
        $this->m_comment = new Comment();
        // supportモデルのインスタンス生成
        $this->m_support = new Support();
    }

    /**
     * ホーム画面の表示
     */
    public function index()
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
