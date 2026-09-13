<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCourierRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $courierId = $this->route('courier');

        return [
            'courier_code' => [
                'required', 'string', 'max:20',
                Rule::unique('m_courier', 'courier_code')->ignore($courierId, 'courier_id'),
            ],
            'courier_name' => ['required', 'string', 'min:3', 'max:150'],
            'courier_phone' => ['nullable', 'string', 'max:30'],
            'courier_email' => ['nullable', 'email', 'max:100'],
            'courier_level' => ['required', 'integer', 'between:1,5'],
            'courier_address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
