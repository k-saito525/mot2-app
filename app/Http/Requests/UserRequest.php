<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // アイコン画像 → ファイルサイズ:8MB, 拡張子:jpg,jpeg,png
            'user_icon' => ['max:8192', 'mimes:jpg,jpeg,png'],
            // 名前 → 必須, 50文字以内
            'name' => ['required', 'max:50'],
            // 表示用のユーザーID → 必須, 半角英(大小)数アンダーバーのみ
            'user_identifier' => ['required', 'regex:/^[a-zA-Z0-9_]+$/', 'min:8', 'max:24'],
            // メールアドレス:必須,(重複確認はコントローラで行う),255文字以内
            'email' => ['nullable:email', 'max:255'],
            // カバー画像 → ファイルサイズ:8MB, 拡張子:jpg,jpeg,png
            'user_cover_image' => ['max:8192', 'mimes:jpg,jpeg,png'],
            // Xリンク → URL形式
            'sns_x' => ['nullable:url', 'max:200'],
            // Facebookリンク → URL形式
            'sns_facebook' => ['nullable:url', 'max:200'],
            // Instagramリンク → URL形式
            'sns_instagram' => ['nullable:url', 'max:200'],
            // 自己紹介 → 400文字以内
            'introduction_text' => ['max:400'],
        ];
    }

    public function messages()
    {
        return [
            'user_icon.max'            => 'アイコン画像のファイルサイズは2MB以内にしてください。',
            'user_icon.mimes'          => 'アイコン画像はjpg,jpeg,pngのいずれかの形式で登録してください。',
            'name.required'            => 'ユーザー名は必ず入力してください。',
            'user_identifier.required' => 'ユーザーIDは必ず入力してください。',
            'user_identifier.regex'    => '使用できない文字が含まれています。',
            'user_identifier.min'      => 'ユーザーIDは8文字以上24文字以内で入力してください。',
            'user_identifier.max'      => 'ユーザーIDは8文字以上24文字以内で入力してください。',
            'email.max'                => 'メールアドレスは255文字以内で入力してください。',
            'user_cover_image.max'     => 'プロフィールカバー画像のファイルサイズは2MB以内にしてください。',
            'user_cover_image.mimes'   => 'プロフィールカバー画像はjpg,jpeg,pngのいずれかの形式で登録してください。',
            'sns_x.url'                => 'URLの形式が正しくありません。',
            'sns_facebook.url'         => 'URLの形式が正しくありません。',
            'sns_instagram.url'        => 'URLの形式が正しくありません。',
            'introduction_text.max'    => '自己紹介は400文字以内で入力してください。',
        ];
    }
}
