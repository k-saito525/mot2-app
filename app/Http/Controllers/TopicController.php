<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopicRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

    public function __construct(private readonly TopicService $topicService) {}

    /**
     * トピック - 一覧画面の表示
     *
     * @param string $page ページ番号
     * @return View
     */
    public function showList(string $page = '1'): View
    {
        $page = max(1, (int)$page);
        $topics = Topic::withAuthor()->latest()->paginate(self::SHOW_CNT_TOPICS, ['*'], 'page', $page);

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
        $topicId = (int)$id;
        $topic = Topic::withAuthor()->find($topicId);
        // 存在しないIDもしくは削除済みの場合は404
        if ($topic === null) {
            abort(404);
        }

        // トピックIDをもとに紐づくコメントを取得
        $comments = Comment::withAuthor()->oldest()->where('topic_id', $topicId)->get();

        // コメント編集権限があるかどうかの確認用(投稿主か否か)
        $userId = Auth::id();

        return view('topic/show/index', [
            'topic' => $topic,
            'comments' => $comments,
            'user_id' => $userId,
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
        $topic = Topic::withAuthor()->find((int)$id);

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
     * トピック - 新規作成実行
     *
     * @return RedirectResponse
     */
    public function store(TopicRequest $request): RedirectResponse
    {
        try {
            $topic          = new Topic();
            $topic->user_id = $request->user()->id;
            $topic->title   = $request->input('topic_title');
            $topic->content = $request->input('topic_detail');
            $topic->save();

            session()->flash('flash_success', __('topics.success.create'));
            return to_route('topic.show.list');
        } catch (\Exception) {
            session()->flash('flash_failed', __('topics.fail.failed'));
            return back();
        }
    }

    /**
     * トピック - 更新実行
     *
     * @param string $id 更新するトピックID
     * @return RedirectResponse
     */
    public function update(TopicRequest $request, string $id): RedirectResponse
    {
        $topic = Topic::find((int)$id);
        if ($topic === null) {
            abort(404);
        }
        try {
            $topic->content = $request->input('topic_detail');
            $topic->save();

            session()->flash('flash_success', __('topics.success.update'));
            return to_route('topic.show.list');
        } catch (\Exception) {
            session()->flash('flash_failed', __('topics.fail.failed'));
            return back();
        }
    }

    /**
     * トピック - 削除実行
     *
     * @param string $id 削除するトピックID
     * @return RedirectResponse
     */
    public function destroy(string $id): RedirectResponse
    {
        $topic = Topic::find((int)$id);
        if ($topic === null || $topic->user_id !== Auth::id()) {
            abort(403);
        }

        $result = $this->topicService->delete((int)$id);
        if ($result) {
            session()->flash('flash_success', __('topics.success.delete'));
            return to_route('topic.show.list');
        }
        session()->flash('flash_failed', __('topics.fail.failed_delete'));
        return back();
    }
}
