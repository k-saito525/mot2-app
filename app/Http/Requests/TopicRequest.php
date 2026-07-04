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
        $topicId = $this->route('id');

        // 新規作成はログイン済みなら誰でも可
        if ($topicId === null) {
            return true;
        }

        // 編集はオーナーのみ
        $topic = Topic::find((int) $topicId);
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
        if ($this->route('id') !== null) {
            return [
                'topic_detail' => ['required', 'string', 'max:400'],
            ];
        }
        return [
            'topic_title' => ['required', 'string', 'max:50'],
            'topic_detail' => ['required', 'string', 'max:400'],
        ];
    }
}
