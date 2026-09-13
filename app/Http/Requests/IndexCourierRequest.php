<?php

namespace App\Http\Requests;

class IndexCourierRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
            'search' => ['sometimes', 'string', 'max:150'],
            'level' => ['sometimes', 'string', 'regex:/^[1-5](,[1-5])*$/'],
            'sort' => ['sometimes', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'level.regex' => 'The level field must be a comma separated list of levels between 1 and 5.',
        ];
    }

    public function validationData(): array
    {
        return $this->query();
    }

    public function perPage(): int
    {
        return (int) ($this->query('per_page') ?: 15);
    }
}
