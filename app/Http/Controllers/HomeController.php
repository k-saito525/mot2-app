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

    private Topic $topic;
    private Comment $comment;

    public function __construct()
    {
        $this->topic = new Topic();
        $this->comment = new Comment();
    }

    /**
     * ホーム画面の表示
     *
     * @return View
     */
    public function index(): View
    {
        // ログインしているユーザー
        $userInfo = Auth::user();
        $userId = $userInfo->id;

        /* 最新のトピックを取得 */
        // $topics = $this->topic->getTopics(self::CNT_SHOW_TOPIC);
        /* ※暫定対応 最新順で6件取得して、1件はおすすめトピックとして表示 */
        $topics = $this->topic->getTopics(6);
        if (!$topics->isEmpty()) {
            $reccTopic = data_get($topics, 0);
            $commentReccTopics = $this->comment->getCommentsByTopicID(data_get($reccTopic, 'id'));
            // 抜き出した最新の1件は削除
            $topics = $topics->slice(1);
        } else {
            /* トピックが1件も存在しない場合はエラー回避のため空配列を作成 */
            $reccTopic = [];
            $commentReccTopics = [];
        }


        return view('home/index', [
            'user_id' => $userId,
            'user_info' => $userInfo,
            'recc_topic' => $reccTopic,
            'comment_recc_topics' => $commentReccTopics,
            'topics' => $topics,
        ]);
    }
}
