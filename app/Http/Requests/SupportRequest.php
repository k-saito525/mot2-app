<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupportRequest extends FormRequest
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
            'message' => ['required', 'max:400'],  // 必須,400文字以内
        ];
    }

    public function messages()
    {
        return [
            'message.required' => '本文は必ず入力してください。',
            'message.max' => '本文は400文字以内で入力してください。',
        ];
    }
}
