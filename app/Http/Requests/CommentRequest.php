<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'comment' => ['required', 'string', 'max:200'],  // コメント:必須,200文字以内
        ];
    }

    public function messages()
    {
        return [
            'comment.required' => 'コメントが未入力です。',
            'comment.max' => 'コメントは200文字以内で入力してください',
        ];
    }
}
