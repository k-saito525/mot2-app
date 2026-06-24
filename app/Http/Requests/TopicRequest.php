<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Topic;

class TopicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $topic_id = $this->input('topic-id');

        // 新規作成はログイン済みなら誰でも可
        if ($topic_id === null) {
            return true;
        }

        // 編集・削除はオーナーのみ
        $topic = Topic::find((int) $topic_id);
        if ($topic === null) {
            return false;
        }
        return $topic->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'topic-title' => ['required', 'string', 'max:50'],  // タイトル:必須,50文字以内
            'topic-detail' => ['required', 'string', 'max:400'], // コンテンツ:必須 400文字以内
        ];
    }
}
