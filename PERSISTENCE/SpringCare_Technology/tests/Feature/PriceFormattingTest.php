<?php

namespace Tests\Feature;

use App\Http\Requests\ProductUnitRequest;
use App\Support\Naira;
use Tests\TestCase;

class PriceFormattingTest extends TestCase
{
    public function test_naira_prices_use_thousands_separators(): void
    {
        $this->assertSame('₦50,000', Naira::format('50000.00'));
        $this->assertSame('₦5,000.50', Naira::format('5000.50'));
        $this->assertSame('₦5,000', Naira::format(5000));
    }

    public function test_grouped_admin_prices_are_normalized_before_validation(): void
    {
        $request = new class extends ProductUnitRequest
        {
            public function prepareForValidation(): void
            {
                parent::prepareForValidation();
            }
        };

        $request->merge(['selling_price' => '50,000']);
        $request->prepareForValidation();
        $this->assertSame('50000', $request->input('selling_price'));

        $request->merge(['selling_price' => '5,000.50']);
        $request->prepareForValidation();
        $this->assertSame('5000.50', $request->input('selling_price'));

        $request->merge(['selling_price' => '50,00']);
        $request->prepareForValidation();
        $this->assertSame('50,00', $request->input('selling_price'));
    }
}