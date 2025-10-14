<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ArticleFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // no auth needed for public API
    }

    public function rules(): array
    {
        return [
            'search'   => 'nullable|string|max:255',
            'source'   => 'nullable|string|exists:sources,api_identifier',
            'category' => 'nullable|string|exists:categories,slug',
            'date'     => 'nullable|date_format:Y-m-d',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors'  => $validator->errors(),
        ], 422));
    }
}
