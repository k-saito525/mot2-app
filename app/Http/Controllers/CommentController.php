<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailComment;
use App\Models\Comment;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;


/**
 * トピックへのコメント関連のコントローラ
 */
class CommentController extends Controller
{
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

        try {
            $comment           = new Comment();
            $comment->comment  = $request->input('comment');
            $comment->topic_id = $topic->id;
            $comment->user_id  = $userInfo->id;
            $comment->save();

            if ($userInfo->id !== $topic->user_id) {
                $topicAuthor = User::approved()->find((int)$topic->user_id);
                if ($topicAuthor !== null) {
                    Mail::to($topicAuthor->email)->send(new MailComment($topicAuthor, $userInfo, $topic->id));
                }
            }

            session()->flash('flash_success', __('comments.success.complete_comment'));
            return to_route('topic.show.detail', ['id' => $topic->id]);
        } catch (\Exception) {
            session()->flash('flash_failed', __('comments.fail.failed_comment'));
            return to_route('topic.show.detail', ['id' => $topic->id]);
        }
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

        try {
            $targetComment->comment = $request->input('comment');
            $targetComment->save();

            session()->flash('flash_success', __('comments.success.complete_edit'));
            return to_route('topic.show.detail', ['id' => $topic->id]);
        } catch (\Exception) {
            session()->flash('flash_failed', __('comments.fail.failed_edit'));
            return back();
        }
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
