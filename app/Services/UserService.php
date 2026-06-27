<?php

namespace App\Services;

use App\Mail\MailChangeEmail;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
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
        $changed_email = false;

        if (!empty(Arr::get($input, 'name', ''))) {
            $user->name = Arr::get($input, 'name', '');
        }

        if (!empty(Arr::get($input, 'user_identifier', ''))) {
            $duplicate = User::where('user_identifier', Arr::get($input, 'user_identifier', ''))
                ->where('id', '!=', $user->id)
                ->exists();
            if ($duplicate) {
                return __('users.fail.duplicate_identifier');
            }
            $user->user_identifier = Arr::get($input, 'user_identifier', '');
        }

        if (!empty(Arr::get($input, 'email', ''))) {
            $old_email = $user->email;
            $user->email = Arr::get($input, 'email', '');
            $changed_email = true;
        }

        $sns_links = $user->sns_links ?? [];
        if (!empty(Arr::get($input, 'sns_x', ''))) {
            $sns_links['x'] = Arr::get($input, 'sns_x', '');
        }
        if (!empty(Arr::get($input, 'sns_facebook', ''))) {
            $sns_links['facebook'] = Arr::get($input, 'sns_facebook', '');
        }
        if (!empty(Arr::get($input, 'sns_instagram', ''))) {
            $sns_links['instagram'] = Arr::get($input, 'sns_instagram', '');
        }
        $user->sns_links = $sns_links;

        if (!empty(Arr::get($input, 'introduction_text', ''))) {
            $user->introduction_text = Arr::get($input, 'introduction_text', '');
        }

        if (!empty(Arr::get($input, 'past-join', []))) {
            $user->past_join = Arr::get($input, 'past-join', []);
        }

        if (!empty(Arr::get($input, 'user_icon'))) {
            $user->user_icon = $this->storeImage(Arr::get($input, 'user_icon'), 'icon', $user->user_icon);
        }

        if (!empty(Arr::get($input, 'user_cover_image'))) {
            $user->user_cover_image = $this->storeImage(Arr::get($input, 'user_cover_image'), 'cover', $user->user_cover_image);
        }

        try {
            $user->save();
            if ($changed_email) {
                Mail::to($user->email)->send(new MailChangeEmail($user, $old_email));
            }
        } catch (\Exception) {
            return __('users.fail.failed_update');
        }

        return '';
    }

    /**
     * 画像をストレージに保存し、既存ファイルを削除してパスを返す
     *
     * @param  UploadedFile $file         アップロードされたファイル
     * @param  string       $directory    保存先ディレクトリ名（例: 'icon', 'cover'）
     * @param  string|null  $existingPath 既存ファイルのパス（削除対象）
     * @return string 保存後のファイルパス
     */
    private function storeImage(UploadedFile $file, string $directory, ?string $existingPath): string
    {
        if (!empty($existingPath)) {
            Storage::disk('public')->delete($directory . '/' . $existingPath);
        }
        $path = $file->store($directory);
        return str_replace('public/' . $directory . '/', '', $path);
    }

    /**
     * ユーザーを承認する
     *
     * @param  int  $id 承認対象のユーザーID
     */
    public function approve(int $id): void
    {
        $user = User::findOrFail($id);
        $user->is_approved = 1;
        $user->save();
    }
}
