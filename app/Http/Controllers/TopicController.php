<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopicRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use App\Models\Topic;
use App\Models\Comment;
use App\Services\TopicService;
use Illuminate\View\View;

/**
 * トピック関連のコントローラ
 */
class TopicController extends Controller
{
    // トピック一覧画面のデフォルト表示件数
    const int SHOW_CNT_TOPICS = 20;

    private Topic $m_topic;

    public function __construct(private readonly TopicService $topicService)
    {
        $this->m_topic = new Topic();
    }

    /**
     * トピック - 一覧画面の表示
     *
     * @param string $page ページ番号
     * @return View
     */
    public function showList(string $page = '1'): View
    {
        $page = max(1, (int)$page);
        $topics = $this->m_topic->getTopicsList(self::SHOW_CNT_TOPICS, $page);

        return view('topic/index', [
            'topics' => $topics,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * トピック - 詳細画面の表示
     *
     * @param string $id  トピックID
     * @return View|RedirectResponse
     */
    public function showDetail(string $id): View|RedirectResponse
    {
        // IDを元にトピックの詳細を取得
        $topic_id = (int)$id;
        $topic = $this->m_topic->getTopicById($topic_id);
        // 存在しないIDもしくは削除済みの場合は404
        if ($topic === null) {
            abort(404);
        }

        // トピックIDをもとに紐づくコメントを取得
        $comments = new Comment()->getCommentsByTopicID($topic_id);

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
     *
     * @return View
     */
    public function showCreate(): View
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
     * @param string $id  編集するトピックのトピックID
     * @return View|RedirectResponse
     */
    public function showEdit(string $id): View|RedirectResponse
    {
        // ログインしているユーザー情報を取得
        $user = Auth::user();
        // トピックIDを元にトピック情報を取得
        $topic = $this->m_topic->getTopicById((int)$id);

        // 不正アクセス対策
        if ($topic === null) {
            abort(404);
        }
        // 投稿者以外は編集できないため一覧に戻す
        if ($user->id !== $topic->user_id) {
            return back();
        }

        return view('topic/edit/index', [
            'topic' => $topic,
            'user' => $user,
        ]);
    }

    /**
     * トピック - 入力内容の確認、保存実行
     *
     * @return RedirectResponse
     */
    public function store(TopicRequest $request): RedirectResponse
    {
        $post = $request->post();
        if (isset($post['delete'])) {
            /* 削除 */

            // 削除実行
            $result = $this->topicService->delete((int)$post['topic-id']);
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

            // 入力データを取得
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
            } catch (\Exception) {
                // 失敗したら入力画面に戻す
                session()->flash('flash_failed', __('topics.fail.failed'));
                return back();
            }
        }
    }
}
