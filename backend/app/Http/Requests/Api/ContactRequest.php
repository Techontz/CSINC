<?php

namespace App\Http\Requests\Api;

use App\Content\ConsultationForm;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    /** Minimum seconds between rendering the form and submitting it. */
    private const MIN_FILL_SECONDS = 3;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $trim = fn ($value) => is_string($value) ? trim($value) : $value;

        $this->merge(array_map($trim, $this->only([
            'first_name', 'last_name', 'email', 'phone', 'company', 'message', 'topic', 'signature',
        ])));

        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+().\-\s]{7,40}$/'],
            'company' => ['nullable', 'string', 'max:190'],
            'address.street' => ['nullable', 'string', 'max:190'],
            'address.line_2' => ['nullable', 'string', 'max:190'],
            'address.city' => ['required', 'string', 'max:120'],
            'address.region' => ['nullable', 'string', 'max:120'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.country' => ['required', 'string', 'max:120'],
            'industry' => ['nullable', Rule::in(ConsultationForm::INDUSTRIES)],
            'growth_stage' => ['required', Rule::in(ConsultationForm::GROWTH_STAGES)],
            'challenges' => ['required', 'array', 'min:1'],
            'challenges.*' => ['string', Rule::in(ConsultationForm::CHALLENGES)],
            'primary_goal' => ['nullable', Rule::in(ConsultationForm::GOALS)],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'topic' => ['nullable', 'string', 'max:190'],
            'preferred_start_date' => ['required', 'date', 'after_or_equal:today', 'before:+2 years'],
            'service_of_interest' => ['nullable', Rule::in(ConsultationForm::SERVICES_OF_INTEREST)],
            'preferred_service' => ['nullable', Rule::in(ConsultationForm::PREFERRED_SERVICES)],
            'product' => ['nullable', 'string', 'max:190'],
            'signature' => ['required', 'string', 'max:190'],
            'consent' => ['accepted'],
            // Spam protection: hidden honeypot must stay empty, and the form must not be submitted instantly.
            'website' => ['nullable', 'max:0'],
            'started_at' => ['required', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
            'challenges.required' => 'Select at least one challenge you are facing.',
            'consent.accepted' => 'Please confirm your consent so we can contact you.',
            'preferred_start_date.after_or_equal' => 'Choose today or a later date.',
            'website.max' => 'Your submission could not be processed.',
            'address.city.required' => 'The city field is required.',
            'address.country.required' => 'The country field is required.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $startedAt = (int) $this->input('started_at') / 1000;
            $elapsed = microtime(true) - $startedAt;

            if ($elapsed < self::MIN_FILL_SECONDS || $elapsed > 60 * 60 * 24) {
                $validator->errors()->add('form', 'Your submission could not be processed. Please refresh the page and try again.');
            }
        });
    }
}
