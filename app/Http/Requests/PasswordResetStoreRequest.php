<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PasswordResetStoreRequest extends FormRequest
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
            'password' => ['max:50', Password::min(8)->letters()->numbers(), 'confirmed'], // 半角英数(a-z,1-9)の混合で8〜50文字
        ];
    }

    /**
     * エラーメッセージのカスタム
     */
    public function messages()
    {
        return [
            'password.confirmed' => 'パスワードが一致しません。',
        ];
    }
}
