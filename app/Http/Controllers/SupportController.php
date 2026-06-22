<?php

namespace App\Http\Controllers;

use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Requests\SupportRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailSupportAdmin;
use App\Models\User;
use App\Models\Topic;
use App\Models\Comment;

/**
 * 運営へのメッセージ関連のコントローラ
 */
class SupportController extends Controller
{

    // userモデルのインスタンス格納用
    private $m_support;

    public function __construct()
    {
        $this->m_support = new Support();
        return $this->m_support;
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * 問い合わせ内容保存
     * 
     * @param SupportRequest $request  入力内容
     */
    public function store(SupportRequest $request)
    {
        // 入力データのバリデート
        $validated = $request->validated();
        // 入力データを取得
        $input = $request->only([
            'message',
            'user_id',
        ]);

        $this->m_support->message = data_get($input, 'message');
        $this->m_support->user_id = data_get($input, 'user_id');
        // 登録実行
        try {
            $this->m_support->save();

            // 管理者へメール送信
            Mail::to(config('mail.to_admin')[App::environment()]['address'])->send(new MailSupportAdmin($this->m_support));

            // 送信成功したら成功メッセージを表示
            session()->flash('flash_success', __('supports.success.complete'));
            return to_route('home.index', '#message');
        } catch (\Exception $e) {
            // 登録失敗したら再度入力フォームに戻してやり直させる
            session()->flash('flash_failed', __('supports.fail.failed'));
            return back();
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Support $support)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Support $support)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Support $support)
    {
        //
    }
}
