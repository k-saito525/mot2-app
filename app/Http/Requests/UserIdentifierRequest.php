<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserIdentifierRequest extends FormRequest
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
            // 半角英(大小)数アンダーバーのみ
            'user_identifier' => ['required', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/', 'min:8', 'max:24'],
        ];
    }

    public function messages()
    {
        return [
            'user_identifier.required' => 'ユーザーIDは必須項目です',
            'user_identifier.unique' => 'このユーザーIDは登録されています。別のユーザーIDを入力してください。',
            'user_identifier.regex' => '使用できない文字が含まれています。',
            'user_identifier.min' => '8文字以上24文字以内で入力してください。',
            'user_identifier.max' => '8文字以上24文字以内で入力してください。',
        ];
    }
}
