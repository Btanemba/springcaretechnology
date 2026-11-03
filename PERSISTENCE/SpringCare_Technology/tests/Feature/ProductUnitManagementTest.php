<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUnitManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_units_can_be_added_and_listed_from_their_product(): void
    {
        $this->actingAs(User::factory()->create());

        $brand = Brand::create(['name' => 'Samsung', 'slug' => 'samsung']);
        $category = Category::create(['name' => 'Smartphones', 'slug' => 'smartphones']);
        $product = Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Samsung S7',
            'slug' => 'samsung-s7',
            'status' => 'published',
        ]);

        $this->postJson(backpack_url("product/{$product->id}/units"), [
            'product_id' => $product->id,
            'sku' => 'S7-001',
            'website' => 'https://example.com/samsung-s7',
            'condition' => 'good',
            'selling_price' => '50,000',
            'status' => 'available',
        ])->assertCreated()
            ->assertJsonPath('unit.sku', 'S7-001')
            ->assertJsonPath('unit.selling_price', '₦50,000');

        $this->assertDatabaseHas('product_units', [
            'product_id' => $product->id,
            'sku' => 'S7-001',
            'website' => 'https://example.com/samsung-s7',
            'selling_price' => '50000.00',
        ]);

        $this->getJson(backpack_url("product/{$product->id}/units"))
            ->assertOk()
            ->assertJsonCount(1, 'units')
            ->assertJsonPath('units.0.sku', 'S7-001');
    }

    public function test_invalid_unit_details_are_rejected_without_creating_a_unit(): void
    {
        $this->actingAs(User::factory()->create());

        $brand = Brand::create(['name' => 'Samsung', 'slug' => 'samsung']);
        $category = Category::create(['name' => 'Smartphones', 'slug' => 'smartphones']);
        $product = Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Samsung S7',
            'slug' => 'samsung-s7',
            'status' => 'published',
        ]);

        $this->postJson(backpack_url("product/{$product->id}/units"), [
            'product_id' => $product->id,
            'sku' => 'S7-INVALID',
            'condition' => 'invalid',
            'selling_price' => '0',
            'status' => 'available',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['condition', 'selling_price']);

        $this->assertDatabaseMissing('product_units', ['sku' => 'S7-INVALID']);
    }
}