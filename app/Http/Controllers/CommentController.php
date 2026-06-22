<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailComment;
use App\Models\Comment;
use App\Models\User;
use App\Models\Topic;


/**
 * トピックへのコメント関連のコントローラ
 */
class CommentController extends Controller
{

    // commentモデルのインスタンス格納用
    private $m_comment;
    // topicモデルのインスタンス格納用
    private $m_topic;
    // userモデルのインスタンス格納用
    private $m_user;

    public function __construct()
    {
        // commentモデルのインスタンス生成
        $this->m_comment = new Comment();
        // topicモデルのインスタンス生成
        $this->m_topic = new Topic();
        // userモデルのインスタンス生成
        $this->m_user = new User();
    }

    /**
     * コメント入力画面の表示
     * 
     * @param int|string $topic_id  コメントするトピックのID
     */
    public function showForm(int|string $topic_id = null)
    {
        // URLにトピックIDがなかったら404
        if (empty($topic_id)) {
            return to_route('404');
        }
        // IDをもとにトピック情報を取得
        $topic = $this->m_topic->getTopicById((int)$topic_id);
        if (empty($topic)) {
            /* 存在しないIDもしくは削除済みの場合は404 */
            return to_route('404');
        }

        // トピックIDから紐づくコメントを取得
        $comments = $this->m_comment->getCommentsByTopicID($topic_id);

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
     */
    public function store(CommentRequest $request)
    {
        // 入力内容チェック
        $validated = $request->validated();
        $input = $request->all();

        /* トピックの存在確認 */
        $topic = $this->m_topic->getTopicById(data_get($input, 'topic_id'));
        if (!isset($topic)) {
            /* 不正なIDもしくはトピックが存在しない場合は一覧に戻す */
            session()->flash('flash_failed', __('comments.fail.not_exist'));
            return to_route('topic.show.list');
        }

        // コメント主(ユーザー)
        $user_info = Auth::user();
        $user_id = $user_info->id;
        // コメント本文
        $comment = data_get($input, 'comment');
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
                } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                // 失敗したら入力画面に戻す
                session()->flash('flash_failed', __('comments.fail.failed_comment'));
                return to_route('topic.show.detail', ['id' => $topic->id]);
            }
        }
    }

    /**
     * コメント編集画面の表示
     *
     * @param int|string $comment_id 編集するコメントID
     */
    public function showEdit(int|string $comment_id)
    {
        // 編集するコメント情報を取得
        $target_comment = $this->m_comment->getCommentsByID($comment_id);
        if (!isset($target_comment)) {
            /* 編集するコメントが存在しない場合は404 */
            return to_route('404');
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
