<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TopicRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Topic;
use App\Models\Comment;
use Carbon\Carbon;

/**
 * トピック関連のコントローラ
 */
class TopicController extends Controller
{

    // 一覧のデフォルト表示件数
    const SHOW_CNT_TOPICS = 20;

    // topicモデルのインスタンス格納用
    private $m_topic;
    // userモデルのインスタンス格納用
    private $m_user;
    // commentモデルのインスタンス格納用
    private $m_comment;
    // 新規作成・編集の入力項目
    private $form_topic = [
        'topic-title',
        'topic-detail',
    ];

    public function __construct()
    {
        // topicモデルのインスタンス生成
        $this->m_topic = new Topic();
        // userモデルのインスタンス生成
        $this->m_user = new User();
        // commentモデルのインスタンス生成
        $this->m_comment = new Comment();
    }

    /**
     * トピック - 一覧画面の表示
     */
    public function showList($page = 1)
    {
        /* 表示するトピックの取得 */
        // ページ番号
        $page = (int)$page;
        if ($page <= 0) {
            /* 不正なページ番号(0以下)の場合は1ページに設定 */
            $page = 1;
        }
        // 表示件数
        $limit = self::SHOW_CNT_TOPICS;
        // 何件目から取得するか設定
        $offset = ($page - 1) * $limit;
        // トピック情報(新しい順)と総件数を取得
        $topic_info = $this->m_topic->getTopicsList($limit, $offset);
        // 取得したトピック情報をトピックと総件数に分ける
        $topics = [];
        $total_cnt = 0;
        if (!empty($topic_info)) {
            $topics = data_get($topic_info, 'topics');
            $total_cnt = data_get($topic_info, 'cnt');
        }

        /* ページネーション */
        // 次のページ番号
        $page_next = '';
        if ($total_cnt > (self::SHOW_CNT_TOPICS * $page)) {
            $page_next = $page + 1;
        }
        // 前のページ番号
        $page_previous = $page - 1;


        // ログインしているユーザーIDを取得(トピック編集ボタンの表示/非表示に使用)
        $user_id = Auth::id();

        return view('topic/index', [
            'topics' => $topics,
            'total_cnt' => $total_cnt,
            'user_id' => $user_id,
            'page' => $page,
            'page_next' => $page_next,
            'page_previous' => $page_previous,
        ]);
    }

    /**
     * トピック - 詳細画面の表示
     * 
     * @param string|null $id  トピックID
     */
    public function showDetail(string|null $id)
    {
        if (empty($id)) {
            /* IDが無い場合は一覧に戻す */
            return to_route('topic.show.list');
        }

        // IDを元にトピックの詳細を取得
        $topic = $this->m_topic->getTopicById((int)$id);
        if (empty($topic)) {
            /* 存在しないIDもしくは削除済みの場合は404 */
            return to_route('404');
        }

        // トピックIDをもとに紐づくコメントを取得
        $comments = $this->m_comment->getCommentsByTopicID((int)$id);

        // コメント編集権限があるかどうかの確認用(投稿主か否か)
        $user_id = Auth::id();

        return view('topic/show/index', [
            'topic' => $topic,
            'comments' => $comments,
            'user_id' => $user_id,
        ]);
    }

    /**
     * トピック新規作成 - 入力画面の表示
     */
    public function showCreate()
    {
        // ユーザー情報(投稿者)を取得
        $user = Auth::user();

        return view('topic/new/index', [
            'user' => $user,
        ]);
    }

    /**
     * トピック編集 - 編集画面の表示
     * 
     * @param string|null $topic_id  編集するトピックのトピックID
     */
    public function showEdit(string $topic_id = null)
    {
        if (empty($topic_id)) {
            /* トピックIDが無い場合は一覧に戻す */
            return to_route('topic.show.list');
        }

        // ログインしているユーザー情報を取得
        $user = Auth::user();
        // トピックIDを元にトピック情報を取得
        $topic = $this->m_topic->getTopicById((int)$topic_id, false);

        /* 不正アクセス対策 */
        if (empty($topic)) {
            /* IDが不正の場合は404 */
            return to_route('404');
        }
        if ($user->id !== $topic->user_id) {
            /* 投稿者以外は編集できないため一覧に戻す */
            return back();
        }

        return view('topic/edit/index', [
            'topic' => $topic,
            'user' => $user,
        ]);
    }

    /**
     * トピック - 入力内容の確認、保存実行
     */
    public function store(TopicRequest $request)
    {
        $post = $request->post();
        if (isset($post['delete'])) {
            /* 削除 */

            // 削除実行
            $result = $this->m_topic->deleteTopic((int)$post['topic-id']);
            if ($result) {
                // 完了したらトピック一覧画面に遷移する
                session()->flash('flash_success', __('topics.success.delete'));
                return to_route('topic.show.list');
            } else {
                // 失敗したらエラーメッセージ
                session()->flash('flash_failed', __('topics.fail.failed_delete'));
                return back();
            }
        } else {
            /* 新規作成・更新 */

            // 入力データのバリデート
            $validated = $request->validated();
            // バリデートOKの場合、取得
            $input = $request->all();

            $message = '';
            try {
                if (isset($input['topic-id'])) {
                    /* 編集の場合はトピック情報を取得 */
                    $topic = $this->m_topic::find((int)$input['topic-id']);
                    // 更新完了メッセージ
                    $message = __('topics.success.update');
                } else {
                    /* 新規作成の場合は投稿者のユーザーIDも保存する */
                    $topic = $this->m_topic;
                    // 投稿者(ログインしているユーザー)の情報を取得
                    $user = Auth::user();
                    $topic->user_id = $user->id;
                    // タイトル
                    $topic->title = Arr::get($input, 'topic-title');
                    // 作成完了メッセージ
                    $message = __('topics.success.create');
                }
                // 本文
                $topic->content = Arr::get($input, 'topic-detail');
                // 保存実行
                $topic->save();

                session()->flash('flash_success', $message);
                // 保存完了したらトピック一覧画面に遷移する
                return to_route('topic.show.list');
            } catch (\Exception $e) {
                // 失敗したら入力画面に戻す
                session()->flash('flash_failed', __('topics.fail.failed'));
                return back();
            }
        }
    }
}
