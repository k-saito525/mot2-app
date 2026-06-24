<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ApplyRequest;
use App\Mail\MailApplyUser;
use App\Mail\MailApplyAdmin;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\View\View;

/*
 * ユーザー会員登録申請
 */

class ApplyController extends Controller
{
    // ユーザー登録申請時のデータ
    private array $formApply = [
        'name',
        'email',
        'past-join',
    ];

    /**
     * ユーザー登録申請 - 入力画面表示
     *
     * @return View
     */
    public function showForm(): View
    {
        // IIMS活動情報
        $activity_list = __('iims_activity');
        return view('apply/index', [
            'activity_list' => $activity_list,
        ]);
    }

    /**
     * ユーザー登録申請 - 入力内容のバリデートから確認画面への遷移
     *
     * @param ApplyRequest $request 入力データ
     * @return RedirectResponse
     */
    public function check(ApplyRequest $request): RedirectResponse
    {
        $input = $request->only($this->formApply);
        // メールアドレスの重複確認
        $m_user = new User();
        $res = $m_user->checkMail($input['email']);
        if (!$res) {
            // エラーメッセージを表示
            session()->flash('flash_failed', __('users.fail.duplicate_mail'));
            return to_route('apply.form');
        }

        $text_past_join = [];
        if (isset($input['past-join'])) {
            /* 確認画面表示用にIIMS活動情報を取得 */
            $activity_list = __('iims_activity');
            foreach ($activity_list as $category => $list) {
                foreach (Arr::get($input, 'past-join') as $key) {
                    $res = '';
                    $res = Arr::get($list, $key);
                    if (!empty($res)) {
                        $text_past_join[$key] = $res;
                        continue;
                    }
                }
            }
        }

        // 入力データをセッションに保存
        $request->session()->put(['form_input' => [
            'name' => Arr::get($input, 'name'),
            'email' => Arr::get($input, 'email'),
            'past-join' => $text_past_join,
        ]]);

        // バリデートにエラーがエラーが無い場合のみ確認画面に遷移
        return to_route('apply.show.confirm');
    }

    /**
     * ユーザー登録申請 - 確認画面の表示
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function showConfirm(Request $request): View|RedirectResponse
    {
        // セッションから入力データを取得
        $form_input = $request->session()->get('form_input');
        if (empty($form_input)) {
            // セッションに値がなければ入力画面に戻す
            return to_route('apply.form');
        }

        return view('apply/confirm/index', [
            'form_input' => $form_input,
        ]);
    }

    /**
     * ユーザー登録申請 - 登録処理
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // 確認画面から渡った入力データをセッションから取得
        $form_input = $request->session()->get('form_input');
        if (empty($form_input)) {
            /* 入力データがセッションに存在しない場合は404 */
            abort(404);
        }
        // メールアドレスから認証用トークンを生成
        $token = Str::random(64);

        // 入力データをUserモデルのインスタンスにセット
        $user = new User();
        $user->name = Arr::get($form_input, 'name');
        $user->email = Arr::get($form_input, 'email');
        // past_join は 'array' cast により配列をそのまま代入できる
        if (!empty($form_input['past-join'])) {
            $user->past_join = array_keys(Arr::get($form_input, 'past-join'));
        }
        $user->verify_token = $token;

        // 登録実行
        try {
            // データベースに保存
            $user->save();

            // 完了メール送信(ユーザー側)
            Mail::to($user->email)->send(new MailApplyUser($form_input));
            // 完了メール送信(管理者側)
            Mail::to(config('mail.to_admin')[App::environment()]['address'])->send(new MailApplyAdmin($form_input));

            // 申請完了画面に遷移
            return to_route('apply.show.complete');
        } catch (\Exception) {
            // 登録失敗したら404を表示
            abort(404);
        }
    }

    /**
     * ユーザー登録申請 - 申請完了画面の表示
     *
     * @return View|RedirectResponse
     */
    public function showComplete(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('form_input')) {
            /* URL直打ちや完了後の再読み込みなどはトップに戻す */
            return to_route('top');
        } else {
            // 登録が完了したユーザー情報をセッションから削除
            $request->session()->forget('form_input');

            return view('apply/complete/index');
        }
    }
}
