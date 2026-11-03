<?php

namespace App\Http\Requests;

use Backpack\CRUD\app\Library\Validation\Rules\ValidUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');

        $this->merge([
            'slug' => is_string($name) ? Str::slug($name) : '',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/',
                Rule::unique('brands', 'slug')->ignore($this->route('id')),
            ],
            'logo' => ValidUpload::field('nullable')->file('image|mimes:jpg,jpeg,png,webp|max:2048'),
            'is_active' => ['required', 'boolean'],
        ];
    }
}