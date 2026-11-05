<?php

namespace App\Http\Requests;

use App\Models\ProductUnit;
use App\Support\Naira;
use Backpack\CRUD\app\Library\Validation\Rules\ValidUploadMultiple;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUnitRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['selling_price' => Naira::normalizeInput($this->input('selling_price'))]);
    }

    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('product_units', 'sku')->ignore($this->route('id'))],
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('product_units', 'serial_number')->ignore($this->route('id'))],
            'website' => ['nullable', 'url', 'max:2048'],
            'storage' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:100'],
            'battery_health' => ['nullable', 'integer', 'between:0,100'],
            'condition' => ['required', Rule::in(array_keys(ProductUnit::CONDITIONS))],
            'defects' => ['nullable', 'string'],
            'included_accessories' => ['nullable', 'string'],
            'photos' => ValidUploadMultiple::field('nullable|array|max:10')
                ->file('image|mimes:jpg,jpeg,png,webp|max:5120'),
            'selling_price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'status' => ['required', Rule::in(array_keys(ProductUnit::STATUSES))],
        ];
    }
}