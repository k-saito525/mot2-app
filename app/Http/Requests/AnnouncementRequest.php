<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
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
            'announcement-title' => ['required', 'string', 'max:50'],  // タイトル:必須,50文字以内
            'announcement-detail' => ['required', 'string', 'max:800'], // 本文:必須 800文字以内
        ];
    }

    public function messages()
    {
        return [
            'announcement-title.required'  => 'タイトルは必ず入力してください。',
            'announcement-title.max'       => 'タイトルは50文字以内で入力してください。',
            'announcement-detail.required' => '本文は必ず入力してください。',
            'announcement-detail.max'      => '本文は800文字以内で入力してください。',
        ];
    }
}
