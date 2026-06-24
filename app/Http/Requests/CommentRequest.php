<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Comment;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $comment_id = $this->input('comment_id');

        // 新規作成はログイン済みなら誰でも可
        if ($comment_id === null) {
            return true;
        }

        // 編集はオーナーのみ
        $comment = Comment::find((int) $comment_id);
        if ($comment === null) {
            return false;
        }
        return $comment->user_id === $this->user()->id;
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

    public function messages(): array
    {
        return [
            'comment.required' => 'コメントが未入力です。',
            'comment.max' => 'コメントは200文字以内で入力してください',
        ];
    }
}
