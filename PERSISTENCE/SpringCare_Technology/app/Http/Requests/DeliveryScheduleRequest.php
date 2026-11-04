<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }

    public function rules(): array
    {
        return [
            'city' => [
                'required',
                'string',
                'max:120',
                Rule::unique('delivery_schedules')->where(fn ($query) => $query
                    ->where('month', $this->input('month'))
                    ->where('year', $this->input('year')))
                    ->ignore($this->route('id')),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,'.(now()->year + 10)],
            'is_published' => ['required', 'boolean'],
        ];
    }
}