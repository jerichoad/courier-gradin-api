<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'courier_code' => ['required', 'string', 'max:20', 'unique:m_courier,courier_code'],
            'courier_name' => ['required', 'string', 'min:3', 'max:150'],
            'courier_phone' => ['nullable', 'string', 'max:30'],
            'courier_email' => ['nullable', 'email', 'max:100'],
            'courier_level' => ['required', 'integer', 'between:1,5'],
            'courier_address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
