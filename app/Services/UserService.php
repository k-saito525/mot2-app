<?php

namespace App\Services;

use App\Mail\MailApprovedUser;
use App\Mail\MailChangeEmail;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserService
{
    /**
     * ユーザープロフィールを更新する
     *
     * アイコン・カバー画像のストレージ操作と、メールアドレス変更時の通知送信も行う。
     *
     * @param  array  $input リクエスト入力値
     * @param  User   $user  更新対象のユーザー
     * @return string エラーメッセージ。成功時は空文字列を返す
     */
    public function updateProfile(array $input, User $user): string
    {
        $changedEmail = false;
        $oldIcon      = null;
        $oldCover     = null;

        if (!empty(Arr::get($input, 'name', ''))) {
            $user->name = Arr::get($input, 'name', '');
        }

        // email/user_identifierの重複確認はUserRequestのuniqueルールで実施済み
        if (!empty(Arr::get($input, 'user_identifier', ''))) {
            $user->user_identifier = Arr::get($input, 'user_identifier', '');
        }

        if (!empty(Arr::get($input, 'email', ''))) {
            $oldEmail = $user->email;
            $user->email = Arr::get($input, 'email', '');
            $changedEmail = true;
        }

        $snsLinks = $user->sns_links ?? [];
        if (!empty(Arr::get($input, 'sns_x', ''))) {
            $snsLinks['x'] = Arr::get($input, 'sns_x', '');
        }
        if (!empty(Arr::get($input, 'sns_facebook', ''))) {
            $snsLinks['facebook'] = Arr::get($input, 'sns_facebook', '');
        }
        if (!empty(Arr::get($input, 'sns_instagram', ''))) {
            $snsLinks['instagram'] = Arr::get($input, 'sns_instagram', '');
        }
        $user->sns_links = $snsLinks;

        if (!empty(Arr::get($input, 'introduction_text', ''))) {
            $user->introduction_text = Arr::get($input, 'introduction_text', '');
        }

        if (!empty(Arr::get($input, 'past_join', []))) {
            $user->past_join = Arr::get($input, 'past_join', []);
        }

        try {
            if (!empty(Arr::get($input, 'user_icon'))) {
                $oldIcon        = $user->user_icon;
                $user->user_icon = $this->storeImage(Arr::get($input, 'user_icon'), 'icon');
            }

            if (!empty(Arr::get($input, 'user_cover_image'))) {
                $oldCover             = $user->user_cover_image;
                $user->user_cover_image = $this->storeImage(Arr::get($input, 'user_cover_image'), 'cover');
            }

            $user->save();
        } catch (\Throwable $e) {
            Log::error('ユーザープロフィールの更新に失敗しました', ['user_id' => $user->id, 'exception' => $e]);
            return __('users.fail.failed_update');
        }

        if ($changedEmail) {
            try {
                Mail::to($user->email)->send(new MailChangeEmail($user, $oldEmail));
            } catch (\Throwable $e) {
                Log::error('メールアドレス変更通知の送信に失敗しました', ['user_id' => $user->id, 'exception' => $e]);
            }
        }

        if ($oldIcon !== null) {
            Storage::disk('public')->delete($oldIcon);
        }
        if ($oldCover !== null) {
            Storage::disk('public')->delete($oldCover);
        }

        return '';
    }

    /**
     * 画像をストレージに保存してパスを返す
     *
     * @param  UploadedFile $file      アップロードされたファイル
     * @param  string       $directory 保存先ディレクトリ名（例: 'icon', 'cover'）
     * @return string 保存後のファイルパス
     * @throws \RuntimeException ファイル保存に失敗した場合
     */
    private function storeImage(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');
        if ($path === false) {
            throw new \RuntimeException('Image upload failed.');
        }
        return str_replace('public/' . $directory . '/', '', $path);
    }

    /**
     * ユーザーを承認する
     *
     * 承認完了通知メールの送信に失敗しても承認処理自体は成功として扱う(ログにのみ残す)。
     *
     * @param  int  $id 承認対象のユーザーID
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 対象ユーザーが存在しない場合
     */
    public function approve(int $id): void
    {
        $user = User::findOrFail($id);
        $user->is_approved = true;
        $user->save();

        try {
            Mail::to($user->email)->send(new MailApprovedUser($user));
        } catch (\Throwable $e) {
            Log::error('承認完了通知メールの送信に失敗しました', ['user_id' => $user->id, 'exception' => $e]);
        }
    }
}
