<?php

namespace Tests\Feature;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_can_be_created_updated_and_deleted(): void
    {
        $category = Category::create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
        ])->refresh();

        $this->assertTrue($category->is_active);
        $this->assertSame(0, $category->sort_order);
        $this->assertNull($category->description);

        $category->update([
            'name' => 'Mobile Phones',
            'slug' => 'mobile-phones',
            'description' => 'Used and pre-order phones.',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Mobile Phones',
            'slug' => 'mobile-phones',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $category->delete();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_validation_rejects_duplicate_slugs_and_invalid_values(): void
    {
        Category::create(['name' => 'Smartphones', 'slug' => 'smartphones']);
        $request = new CategoryRequest;

        $validator = Validator::make([
            'name' => '',
            'slug' => 'smartphones',
            'is_active' => 'invalid',
            'sort_order' => -1,
        ], $request->rules());

        $this->assertSame(
            ['name', 'slug', 'is_active', 'sort_order'],
            $validator->errors()->keys(),
        );

        $validator = Validator::make([
            'name' => 'Laptops',
            'slug' => 'Used Laptops!',
            'is_active' => true,
            'sort_order' => 0,
        ], $request->rules());

        $this->assertTrue($validator->errors()->has('slug'));
    }

    public function test_slug_is_generated_from_name_instead_of_submitted_value(): void
    {
        $request = new class extends CategoryRequest
        {
            public function prepareForValidation(): void
            {
                parent::prepareForValidation();
            }
        };

        $request->merge(['name' => 'Smart Phones & Tablets', 'slug' => 'manually-edited']);
        $request->prepareForValidation();

        $this->assertSame('smart-phones-and-tablets', $request->input('slug'));

        $request->merge(['name' => 'Used Laptops']);
        $request->prepareForValidation();

        $this->assertSame('used-laptops', $request->input('slug'));

        $request->merge(['name' => ['invalid']]);
        $request->prepareForValidation();

        $this->assertSame('', $request->input('slug'));
    }

    public function test_guests_cannot_access_category_management(): void
    {
        $this->get(backpack_url('category'))->assertRedirect();
        $this->get(backpack_url('category/create'))->assertRedirect();
    }
}