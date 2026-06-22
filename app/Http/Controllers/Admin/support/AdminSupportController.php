<?php

namespace App\Http\Controllers\Admin\support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Mail\MailApprovedUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Support;

class AdminSupportController extends Controller
{
    // supportモデルのインスタンス
    public $m_support;

    public function __construct()
    {
        $this->m_support = new Support();
        return $this->m_support;
    }

    /**
     * メッセージ一覧画面の表示
     */
    public function showList()
    {
        // 承認待ちのユーザー情報を取得
        $messages = $this->m_support->getMessages();

        return view('admin/support/index', [
            'messages' => $messages,
        ]);
    }
}
