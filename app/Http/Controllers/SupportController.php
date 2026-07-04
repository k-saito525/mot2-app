<?php

namespace App\Http\Controllers;

use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Requests\SupportRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailSupportAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * 運営へのメッセージ関連のコントローラ
 */
class SupportController extends Controller
{
    private Support $support;

    public function __construct()
    {
        $this->support = new Support();
    }


    /**
     * 問い合わせ内容保存
     *
     * @param SupportRequest $request  入力内容
     * @return RedirectResponse
     */
    public function store(SupportRequest $request): RedirectResponse
    {
        // 入力データを取得
        $input = $request->only([
            'message',
            'user_id',
        ]);

        $this->support->message = Arr::get($input, 'message');
        $this->support->user_id = Arr::get($input, 'user_id');
        // 登録実行
        try {
            $this->support->save();

            // 管理者へメール送信
            Mail::to(config('mail.to_admin')[App::environment()]['address'])->send(new MailSupportAdmin($this->support));

            // 送信成功したら成功メッセージを表示
            session()->flash('flash_success', __('supports.success.complete'));
            return to_route('home.index', '#message');
        } catch (\Exception) {
            // 登録失敗したら再度入力フォームに戻してやり直させる
            session()->flash('flash_failed', __('supports.fail.failed'));
            return back();
        }
    }
}
