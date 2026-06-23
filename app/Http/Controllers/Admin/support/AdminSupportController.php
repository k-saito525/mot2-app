<?php

namespace App\Http\Controllers\Admin\support;

use App\Http\Controllers\Controller;
use App\Models\Support;

class AdminSupportController extends Controller
{
    /**
     * メッセージ一覧画面の表示
     */
    public function showList()
    {
        // 承認待ちのユーザー情報を取得
        $messages = new Support()->getMessages();

        return view('admin/support/index', [
            'messages' => $messages,
        ]);
    }
}
