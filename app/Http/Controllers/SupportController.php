<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupportRequest;
use App\Services\SupportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * 運営へのメッセージ関連のコントローラ
 */
class SupportController extends Controller
{
    public function __construct(private readonly SupportService $supportService) {}

    /**
     * 問い合わせ内容保存
     *
     * @param SupportRequest $request  入力内容
     * @return RedirectResponse
     */
    public function store(SupportRequest $request): RedirectResponse
    {
        $input = [
            'message' => $request->input('message'),
            'user_id' => Auth::id(),
        ];

        $result = $this->supportService->create($input);
        if ($result) {
            // 送信成功したら成功メッセージを表示
            session()->flash('flash_success', __('supports.success.complete'));
            return to_route('home.index', '#message');
        }
        // 登録失敗したら再度入力フォームに戻してやり直させる
        session()->flash('flash_failed', __('supports.fail.failed'));
        return back();
    }
}
