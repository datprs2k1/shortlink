<?php

namespace App\Http\Requests\Domain;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDomainRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $domainId = $this->route('domain'); // Get the domain ID from route
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:domains,name,' . $domainId,
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The domain name is required.',
            'name.unique' => 'This domain name is already registered.',
            'name.regex' => 'Please provide a valid domain name format.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'domain name',
        ];
    }
}
