<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailComment;
use App\Models\Comment;
use App\Models\User;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;


/**
 * トピックへのコメント関連のコントローラ
 */
class CommentController extends Controller
{

    private Comment $m_comment;
    private Topic $m_topic;
    private User $m_user;

    public function __construct()
    {
        $this->m_comment = new Comment();
        $this->m_topic = new Topic();
        $this->m_user = new User();
    }

    /**
     * コメント入力画面の表示
     *
     * @param string $topic_id  コメントするトピックのID
     * @return View
     */
    public function showForm(string $topic_id): View
    {
        // IDをもとにトピック情報を取得
        $topic = $this->m_topic->getTopicById((int)$topic_id);
        if ($topic === null) {
            /* 存在しないIDもしくは削除済みの場合は404 */
            abort(404);
        }

        // トピックIDから紐づくコメントを取得
        $comments = $this->m_comment->getCommentsByTopicID((int)$topic_id);

        // コメント主の情報
        $user = Auth::user();

        return view('topic/comment/index', [
            'topic' => $topic,
            'comments' => $comments,
            'user' => $user,
        ]);
    }

    /**
     * コメント保存
     *
     * @param CommentRequest $request
     * @return RedirectResponse
     */
    public function store(CommentRequest $request): RedirectResponse
    {
        // 入力内容チェック
        $input = $request->all();

        /* トピックの存在確認 */
        $topic = $this->m_topic->getTopicById(Arr::get($input, 'topic_id'));
        if (!isset($topic)) {
            /* 不正なIDもしくはトピックが存在しない場合は一覧に戻す */
            session()->flash('flash_failed', __('comments.fail.not_exist'));
            return to_route('topic.show.list');
        }

        // コメント主(ユーザー)
        $user_info = Auth::user();
        $user_id = $user_info->id;
        // コメント本文
        $comment = Arr::get($input, 'comment');
        if (isset($input['comment_id'])) {
            /* 編集 */
            // コメントの存在チェック
            $m_comment = $this->m_comment::whereNull('deleted_at')
                ->where('id', $input['comment_id'])
                ->first();
            if (isset($comment)) {
                $m_comment->comment = $comment;

                try {
                    // 更新実行
                    $m_comment->save();

                    // 保存完了したらトピック詳細画面に遷移する
                    session()->flash('flash_success', __('comments.success.complete_edit'));
                    return to_route('topic.show.detail', ['id' => $topic->id]);
                } catch (\Exception) {
                    // 失敗したら入力画面に戻す
                    session()->flash('flash_failed', __('comments.fail.failed_edit'));
                    return back();
                }
            } else {
                // コメントが存在しない場合は処理せずトピック詳細に戻す
                return to_route('topic.show.detail', ['id' => $topic->id]);
            }
        } else {
            /* 新規作成*/
            // コメント本文
            $this->m_comment->comment = $comment;
            // トピックID
            $this->m_comment->topic_id = data_get($topic, 'id');
            // ユーザーID(コメント主)
            $this->m_comment->user_id = $user_id;

            try {
                // 更新実行
                $this->m_comment->save();
                // コメント先のトピック作成者にメール送信
                if ($user_id !== $topic->user_id) {
                    /* コメント主がトピック作成者では無い場合のみ送信 */
                    // トピック作成者情報
                    $topic_author = $this->m_user->getUserById((int)$topic->user_id);
                    Mail::to($topic_author->email)->send(new MailComment($topic_author, $user_info, $topic->id));
                }
                // 保存完了したらトピック詳細画面に遷移する
                session()->flash('flash_success', __('comments.success.complete_comment'));
                return to_route('topic.show.detail', ['id' => $topic->id]);
            } catch (\Exception) {
                // 失敗したら入力画面に戻す
                session()->flash('flash_failed', __('comments.fail.failed_comment'));
                return to_route('topic.show.detail', ['id' => $topic->id]);
            }
        }
    }

    /**
     * コメント編集画面の表示
     *
     * @param string $comment_id 編集するコメントID
     * @return View|RedirectResponse
     */
    public function showEdit(string $comment_id): View|RedirectResponse
    {
        // 編集するコメント情報を取得
        $target_comment = $this->m_comment->getCommentsByID($comment_id);
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
