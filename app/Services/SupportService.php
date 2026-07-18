<?php

namespace App\Services;

use App\Mail\MailSupportAdmin;
use App\Models\Support;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SupportService
{
    /**
     * 運営へのメッセージを保存し、管理者へ通知メールを送信する
     *
     * 通知メールの送信失敗はメッセージ保存自体の失敗とはしない。
     *
     * @param  array $input リクエスト入力値(message, user_id)
     * @return bool true: 登録成功、false: 登録失敗
     */
    public function create(array $input): bool
    {
        try {
            $support          = new Support();
            $support->message = Arr::get($input, 'message');
            $support->user_id = Arr::get($input, 'user_id');
            $support->save();
        } catch (\Throwable $e) {
            Log::error('サポートメッセージの保存に失敗しました', ['user_id' => Arr::get($input, 'user_id'), 'exception' => $e]);
            return false;
        }

        try {
            Mail::to(config('mail.to_admin')[App::environment()]['address'])->send(new MailSupportAdmin($support));
        } catch (\Throwable $e) {
            Log::error('サポート通知メールの送信に失敗しました', ['support_id' => $support->id, 'exception' => $e]);
        }

        return true;
    }
}
