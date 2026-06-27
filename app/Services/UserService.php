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
        $old_icon      = null;
        $old_cover     = null;

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

        try {
            if (!empty(Arr::get($input, 'user_icon'))) {
                $old_icon        = $user->user_icon;
                $user->user_icon = $this->storeImage(Arr::get($input, 'user_icon'), 'icon');
            }

            if (!empty(Arr::get($input, 'user_cover_image'))) {
                $old_cover             = $user->user_cover_image;
                $user->user_cover_image = $this->storeImage(Arr::get($input, 'user_cover_image'), 'cover');
            }

            $user->save();
            if ($changed_email) {
                Mail::to($user->email)->send(new MailChangeEmail($user, $old_email));
            }
        } catch (\Exception) {
            return __('users.fail.failed_update');
        }

        if ($old_icon !== null) {
            Storage::disk('public')->delete('icon/' . $old_icon);
        }
        if ($old_cover !== null) {
            Storage::disk('public')->delete('cover/' . $old_cover);
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
        $path = $file->store($directory);
        if ($path === false) {
            throw new \RuntimeException('Image upload failed.');
        }
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
