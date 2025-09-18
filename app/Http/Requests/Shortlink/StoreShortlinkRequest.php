<?php

namespace App\Http\Requests\Shortlink;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShortlinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'original_url' => [
                'required',
                'url',
                'max:2048',
            ],
            'domain_id' => [
                'required',
                'integer',
                'exists:domains,id',
            ],
            'code_type' => [
                'required',
                'string',
                'in:auto,custom',
            ],
            'short_code' => [
                'nullable',
                'required_if:code_type,custom',
                'string',
                'alpha_dash',
                'min:3',
                'max:50',
                Rule::unique('shortlinks', 'short_code')
                    ->where('domain_id', $this->input('domain_id')),
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
            ],
            'password' => [
                'nullable',
                'string',
                'min:4',
                'max:100',
            ],
            'tags' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'original_url.required' => 'The original URL is required.',
            'original_url.url' => 'Please provide a valid URL.',
            'original_url.max' => 'The URL cannot be longer than 2048 characters.',
            'domain_id.required' => 'Please select a domain.',
            'domain_id.exists' => 'The selected domain is invalid.',
            'short_code.required_if' => 'Short code is required when using custom code type.',
            'short_code.unique' => 'This short code is already taken for the selected domain.',
            'short_code.alpha_dash' => 'Short code can only contain letters, numbers, dashes, and underscores.',
            'expires_at.after' => 'Expiration date must be in the future.',
        ];
    }

    public function attributes(): array
    {
        return [
            'original_url' => 'original URL',
            'domain_id' => 'domain',
            'short_code' => 'short code',
            'expires_at' => 'expiration date',
        ];
    }
}