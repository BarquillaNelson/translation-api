<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranslationRequest extends FormRequest
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
        if($this->isMethod('post')) {
            return [
                'key' => 'required|string|unique:translations,key',
                'content' => 'required|array', 
                'tags' => 'nullable|array',
            ];
        } else if($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'key' => 'nullable|string|unique:translations,key',
                'content' => 'nullable|array', 
                'tags' => 'nullable|array',
            ];
        } else if($this->isMethod('get')) {
            return [
                'pages' => 'nullable',
                'search' => 'nullable|string',
            ];
        } else if($this->isMethod('delete')) {
            return [
                'id' => 'required|exists:translations,id',
            ];
        }
    }
}
