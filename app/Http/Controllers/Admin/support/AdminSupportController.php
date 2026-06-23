<?php

namespace App\Http\Controllers\Admin\support;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\View\View;

class AdminSupportController extends Controller
{
    /**
     * メッセージ一覧画面の表示
     *
     * @return View
     */
    public function showList(): View
    {
        // 承認待ちのユーザー情報を取得
        $messages = new Support()->getMessages();

        return view('admin/support/index', [
            'messages' => $messages,
        ]);
    }
}
