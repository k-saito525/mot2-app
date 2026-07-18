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
    // ホーム画面のトピック一覧に表示する件数(おすすめ枠を除く)
    const int CNT_SHOW_TOPIC = 5;
    // おすすめ枠として先頭に表示する件数
    const int CNT_RECOMMENDED_TOPIC = 1;

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

        // 最新のトピックを取得(先頭1件をおすすめ枠として表示するため、一覧表示件数+1件取得する)
        $topics = Topic::withAuthor()->latest()->limit(self::CNT_SHOW_TOPIC + self::CNT_RECOMMENDED_TOPIC)->get();
        if (!$topics->isEmpty()) {
            $reccTopic = data_get($topics, 0);
            $commentReccTopics = Comment::withAuthor()->oldest()->where('topic_id', data_get($reccTopic, 'id'))->get();
            // おすすめ枠として抜き出した先頭の1件は一覧から除く
            $topics = $topics->slice(self::CNT_RECOMMENDED_TOPIC);
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
