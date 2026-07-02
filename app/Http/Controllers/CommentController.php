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

    private Comment $m_comment;
    private Topic $m_topic;

    public function __construct()
    {
        $this->m_comment = new Comment();
        $this->m_topic = new Topic();
    }

    /**
     * コメント入力画面の表示
     *
     * @param string $id  コメントするトピックのID
     * @return View
     */
    public function showForm(string $id): View
    {
        // IDをもとにトピック情報を取得
        $topic = $this->m_topic->getTopicById((int)$id);
        if ($topic === null) {
            /* 存在しないIDもしくは削除済みの場合は404 */
            abort(404);
        }

        // トピックIDから紐づくコメントを取得
        $comments = $this->m_comment->getCommentsByTopicID((int)$id);

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
        $topic = $this->m_topic->getTopicById((int) $request->input('topic_id'));
        if ($topic === null) {
            session()->flash('flash_failed', __('comments.fail.not_exist'));
            return to_route('topic.show.list');
        }

        $user_info = Auth::user();

        try {
            $comment           = new Comment();
            $comment->comment  = $request->input('comment');
            $comment->topic_id = $topic->id;
            $comment->user_id  = $user_info->id;
            $comment->save();

            if ($user_info->id !== $topic->user_id) {
                $topic_author = User::approved()->find((int)$topic->user_id);
                Mail::to($topic_author->email)->send(new MailComment($topic_author, $user_info, $topic->id));
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
        $target_comment = $this->m_comment->getCommentByID((int) $id);
        if ($target_comment === null) {
            abort(404);
        }

        $topic = $this->m_topic->getTopicById((int) $target_comment->topic_id);
        if ($topic === null) {
            session()->flash('flash_failed', __('comments.fail.not_exist'));
            return to_route('topic.show.list');
        }

        try {
            $target_comment->comment = $request->input('comment');
            $target_comment->save();

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
        $target_comment = $this->m_comment->getCommentByID((int) $id);
        if (!isset($target_comment)) {
            /* 編集するコメントが存在しない場合は404 */
            abort(404);
        }

        // トピックを取得
        $topic = $this->m_topic->getTopicById((int)$target_comment->topic_id);
        if (!isset($topic)) {
            /* トピックが存在しない場合は一覧に戻す */
            session()->flash('flash_failed', __('comments.fail.not_exist'));
            return to_route('topic.show.list');
        }


        // コメント主以外のアクセスの場合は不正
        $user_id = Auth::id();


        if (data_get($target_comment, 'user_id') !== $user_id) {
            /* コメント主以外のアクセスの場合はトピック詳細画面に戻す */
            return to_route('topic.show.detail', ['id' => $topic->id]);
        }

        // トピックIDから紐づくコメントを取得
        $comments = $this->m_comment->getCommentsByTopicID($topic->id);
        return view('comment/edit/index', [
            'topic' => $topic,
            'comments' => $comments,
            'target_comment' => $target_comment,
            'user_id' => $user_id,
        ]);
    }
}
