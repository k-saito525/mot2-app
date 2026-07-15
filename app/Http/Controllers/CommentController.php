<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use App\Models\Topic;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;


/**
 * トピックへのコメント関連のコントローラ
 */
class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService) {}

    /**
     * コメント入力画面の表示
     *
     * @param string $id  コメントするトピックのID
     * @return View
     */
    public function showForm(string $id): View
    {
        // IDをもとにトピック情報を取得
        $topic = Topic::withAuthor()->find((int)$id);
        if ($topic === null) {
            /* 存在しないIDもしくは削除済みの場合は404 */
            abort(404);
        }

        // トピックIDから紐づくコメントを取得
        $comments = Comment::withAuthor()->oldest()->where('topic_id', (int)$id)->get();

        // コメント主の情報
        $user = Auth::user();

        return view('topic/comment/index', [
            'topic' => $topic,
            'comments' => $comments,
            'user' => $user,
        ]);
    }

    /**
     * コメント新規作成
     *
     * @param CommentRequest $request
     * @return RedirectResponse
     */
    public function store(CommentRequest $request): RedirectResponse
    {
        $topic = Topic::withAuthor()->find((int) $request->input('topic_id'));
        if ($topic === null) {
            session()->flash('flash_failed', __('comments.fail.not_exist'));
            return to_route('topic.show.list');
        }

        $userInfo = Auth::user();

        $result = $this->commentService->create($topic, $userInfo, $request->input('comment'));
        if ($result) {
            session()->flash('flash_success', __('comments.success.complete_comment'));
        } else {
            session()->flash('flash_failed', __('comments.fail.failed_comment'));
        }
        return to_route('topic.show.detail', ['id' => $topic->id]);
    }

    /**
     * コメント更新
     *
     * @param CommentRequest $request
     * @param string $id 更新するコメントID
     * @return RedirectResponse
     */
    public function update(CommentRequest $request, string $id): RedirectResponse
    {
        $targetComment = Comment::withAuthor()->find((int) $id);
        if ($targetComment === null) {
            abort(404);
        }

        $topic = Topic::withAuthor()->find((int) $targetComment->topic_id);
        if ($topic === null) {
            session()->flash('flash_failed', __('comments.fail.not_exist'));
            return to_route('topic.show.list');
        }

        $result = $this->commentService->updateComment($targetComment, $request->input('comment'));
        if ($result) {
            session()->flash('flash_success', __('comments.success.complete_edit'));
            return to_route('topic.show.detail', ['id' => $topic->id]);
        }
        session()->flash('flash_failed', __('comments.fail.failed_edit'));
        return back();
    }

    /**
     * コメント編集画面の表示
     *
     * @param string $id 編集するコメントID
     * @return View|RedirectResponse
     */
    public function showEdit(string $id): View|RedirectResponse
    {
        // 編集するコメント情報を取得
        $targetComment = Comment::withAuthor()->find((int) $id);
        if (!isset($targetComment)) {
            /* 編集するコメントが存在しない場合は404 */
            abort(404);
        }

        // トピックを取得
        $topic = Topic::withAuthor()->find((int)$targetComment->topic_id);
        if (!isset($topic)) {
            /* トピックが存在しない場合は一覧に戻す */
            session()->flash('flash_failed', __('comments.fail.not_exist'));
            return to_route('topic.show.list');
        }


        // コメント主以外のアクセスの場合は不正
        $userId = Auth::id();


        if (data_get($targetComment, 'user_id') !== $userId) {
            /* コメント主以外のアクセスの場合はトピック詳細画面に戻す */
            return to_route('topic.show.detail', ['id' => $topic->id]);
        }

        // トピックIDから紐づくコメントを取得
        $comments = Comment::withAuthor()->oldest()->where('topic_id', $topic->id)->get();
        return view('comment/edit/index', [
            'topic' => $topic,
            'comments' => $comments,
            'target_comment' => $targetComment,
            'user_id' => $userId,
        ]);
    }
}
