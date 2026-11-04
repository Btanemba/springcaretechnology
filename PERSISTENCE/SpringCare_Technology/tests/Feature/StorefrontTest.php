<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_shop_render_products_with_available_units(): void
    {
        $brand = Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'is_active' => true,
        ]);
        $category = Category::create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $product = Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Samsung S7',
            'slug' => 'samsung-s7',
            'status' => 'published',
        ]);
        $product->units()->create([
            'sku' => 'S7-001',
            'website' => 'https://example.com/samsung-s7',
            'condition' => 'good',
            'selling_price' => 50000,
            'status' => 'available',
        ]);

        $this->get('/')->assertOk();
        $this->get('/shop')->assertOk();
        $this->get('/shop/samsung-s7')
            ->assertOk()
            ->assertDontSee('Original source website')
            ->assertDontSee('https://example.com/samsung-s7');
    }
}