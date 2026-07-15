<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ApplyRequest;
use App\Mail\MailApplyUser;
use App\Mail\MailApplyAdmin;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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
        'past_join',
    ];

    /**
     * ユーザー登録申請 - 入力画面表示
     *
     * @return View
     */
    public function showForm(): View
    {
        // IIMS活動情報
        $activityList = __('iims_activity');
        return view('apply/index', [
            'activity_list' => $activityList,
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
        if (User::where('email', $input['email'])->exists()) {
            session()->flash('flash_failed', __('users.fail.duplicate_mail'));
            return to_route('apply.form');
        }

        $textPastJoin = [];
        if (isset($input['past_join'])) {
            /* 確認画面表示用にIIMS活動情報を取得 */
            $activityList = __('iims_activity');
            foreach ($activityList as $list) {
                foreach (Arr::get($input, 'past_join') as $key) {
                    $res = '';
                    $res = Arr::get($list, $key);
                    if (!empty($res)) {
                        $textPastJoin[$key] = $res;
                        continue;
                    }
                }
            }
        }

        // 入力データをセッションに保存
        $request->session()->put(['form_input' => [
            'name' => Arr::get($input, 'name'),
            'email' => Arr::get($input, 'email'),
            'past_join' => $textPastJoin,
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
        $formInput = $request->session()->get('form_input');
        if (empty($formInput)) {
            // セッションに値がなければ入力画面に戻す
            return to_route('apply.form');
        }

        return view('apply/confirm/index', [
            'form_input' => $formInput,
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
        $formInput = $request->session()->get('form_input');
        if (empty($formInput)) {
            /* 入力データがセッションに存在しない場合は404 */
            abort(404);
        }
        // メールアドレスから認証用トークンを生成
        $token = Str::random(64);

        // 入力データをUserモデルのインスタンスにセット
        $user = new User();
        $user->name = Arr::get($formInput, 'name');
        $user->email = Arr::get($formInput, 'email');
        // past_join は 'array' cast により配列をそのまま代入できる
        if (!empty($formInput['past_join'])) {
            $user->past_join = array_keys(Arr::get($formInput, 'past_join'));
        }
        $user->verify_token = $token;

        // 登録実行
        try {
            // データベースに保存
            $user->save();
        } catch (\Exception $e) {
            Log::error('ユーザー登録申請の保存に失敗しました', ['email' => $user->email, 'exception' => $e]);
            // 登録失敗したら404を表示
            abort(404);
        }

        // 完了メール送信。送信失敗しても登録自体は完了しているため、ログのみ残す
        try {
            // 完了メール送信(ユーザー側)
            Mail::to($user->email)->send(new MailApplyUser($formInput));
            // 完了メール送信(管理者側)
            Mail::to(config('mail.to_admin')[App::environment()]['address'])->send(new MailApplyAdmin($formInput));
        } catch (\Exception $e) {
            Log::error('登録申請完了通知メールの送信に失敗しました', ['user_id' => $user->id, 'exception' => $e]);
        }

        // 申請完了画面に遷移
        return to_route('apply.show.complete');
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
