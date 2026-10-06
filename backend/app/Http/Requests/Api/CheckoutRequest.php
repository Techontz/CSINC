<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }

        if (is_array($this->input('items'))) {
            $this->merge(['items' => array_values(array_unique(array_filter($this->input('items'), 'is_string')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:40'],
            'items.*' => ['required', 'string', 'max:190', 'alpha_dash'],
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'accept_terms' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Your cart is empty.',
            'accept_terms.accepted' => 'Please accept the Terms & Conditions and Refund Policy to continue.',
        ];
    }
}
