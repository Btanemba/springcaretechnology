<?php

namespace Tests\Feature;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_brands_can_be_created_updated_and_deleted(): void
    {
        $brand = Brand::create(['name' => 'Apple', 'slug' => 'apple'])->refresh();

        $this->assertTrue($brand->is_active);
        $this->assertNull($brand->logo);

        $brand->update(['name' => 'Samsung', 'slug' => 'samsung', 'is_active' => false]);

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Samsung',
            'slug' => 'samsung',
            'is_active' => false,
        ]);

        $brand->delete();

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    public function test_slug_is_generated_from_name_instead_of_submitted_value(): void
    {
        $request = new class extends BrandRequest
        {
            public function prepareForValidation(): void
            {
                parent::prepareForValidation();
            }
        };

        $request->merge(['name' => 'Harman & Kardon', 'slug' => 'manually-edited']);
        $request->prepareForValidation();

        $this->assertSame('harman-and-kardon', $request->input('slug'));

        $request->merge(['name' => 'Samsung']);
        $request->prepareForValidation();

        $this->assertSame('samsung', $request->input('slug'));
    }

    public function test_brand_validation_rejects_duplicate_slugs_and_invalid_logos(): void
    {
        CRUD::setModel(Brand::class);
        Brand::create(['name' => 'Apple', 'slug' => 'apple']);
        $request = new BrandRequest;

        $validator = Validator::make([
            'name' => 'Apple',
            'slug' => 'apple',
            'logo' => UploadedFile::fake()->create('logo.txt', 10, 'text/plain'),
            'is_active' => true,
        ], $request->rules());

        $this->assertTrue($validator->errors()->has('slug'));
        $this->assertTrue($validator->errors()->has('logo'));
    }

    public function test_brand_logo_is_optional(): void
    {
        CRUD::setModel(Brand::class);
        $request = new BrandRequest;

        $validator = Validator::make([
            'name' => 'Apple',
            'slug' => 'apple',
            'is_active' => true,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_deleting_a_brand_removes_its_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('brands/apple.png', 'logo-content');
        $brand = Brand::create([
            'name' => 'Apple',
            'slug' => 'apple',
            'logo' => 'brands/apple.png',
        ]);

        $brand->delete();

        Storage::disk('public')->assertMissing('brands/apple.png');
    }

    public function test_guests_cannot_access_brand_management(): void
    {
        $this->get(backpack_url('brand'))->assertRedirect();
        $this->get(backpack_url('brand/create'))->assertRedirect();
    }
}