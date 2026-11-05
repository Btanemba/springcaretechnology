<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductUnitRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Support\Naira;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\JsonResponse;

class ProductCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use UpdateOperation;

    public function setup()
    {
        CRUD::setModel(Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/product');
        CRUD::setEntityNameStrings('product', 'products');
    }

    protected function setupListOperation()
    {
        CRUD::addColumn([
            'name' => 'custom_actions',
            'type' => 'view',
            'view' => 'vendor.backpack.crud.columns.custom_button',
            'orderable' => false,
            'searchable' => false,
            'visibleInExport' => false,
        ]);
        CRUD::column('name')->type('text')->label('Name');
        CRUD::column('category_id')->type('select')->entity('category')
            ->model(Category::class)->attribute('name')->label('Category');
        CRUD::column('brand_id')->type('select')->entity('brand')
            ->model(Brand::class)->attribute('name')->label('Brand');
        CRUD::column('model_number')->type('text')->label('Model Number');
        CRUD::column('status')->type('select_from_array')->options(Product::STATUSES)->label('Status');
        $this->crud->removeButton('preview');
        $this->crud->removeButton('update');
        $this->crud->removeButton('revisions');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('show');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductRequest::class);
        CRUD::addSaveAction([
            'name' => 'save_and_add_units',
            'button_text' => 'Save & add units',
            'redirect' => fn ($crud, $request, $itemId) => backpack_url('product/'.$itemId.'/edit?open_units=1'),
        ]);
        if ($this->crud->getCurrentOperation() === 'create') {
            CRUD::setOperationSetting('defaultSaveAction', 'save_and_add_units');
        }

        CRUD::field('name')->type('text')->label('Name')->tab('Details');
        CRUD::field('slug')->type('text')->label('Slug')->tab('Details')->attributes([
            'readonly' => 'readonly',
            'style' => 'background-color: var(--tblr-bg-surface-secondary, #e9ecef); color: var(--tblr-secondary, #6c757d);',
        ]);
        CRUD::field('category_id')->type('select')->entity('category')
            ->model(Category::class)->attribute('name')->label('Category')->allows_null(true)->default(null)->tab('Details');
        CRUD::field('brand_id')->type('select')->entity('brand')
            ->model(Brand::class)->attribute('name')->label('Brand')->allows_null(true)->default(null)->tab('Details');
        CRUD::field('model_number')->type('text')->label('Model Number')->tab('Details');
        CRUD::field('status')->type('select_from_array')->options(Product::STATUSES)
            ->default('draft')->allows_null(false)->label('Status')->tab('Details');
        if ($this->crud->getCurrentOperation() === 'update') {
            CRUD::field('units_manager')->type('custom_html')->label('Inventory units')->tab('Units')
                ->value(view('vendor.backpack.crud.fields.product_units')->render());
        }

        CRUD::field('slug_script')->type('custom_html')->tab('Details')->value(<<<'HTML'
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const nameInput = document.querySelector('input[name="name"]');
                    const slugInput = document.querySelector('input[name="slug"]');

                    if (!nameInput || !slugInput) return;

                    const updateSlug = function () {
                        slugInput.value = nameInput.value
                            .normalize('NFKD')
                            .replace(/[\u0300-\u036f]/g, '')
                            .toLowerCase()
                            .replace(/&/g, ' and ')
                            .replace(/[^a-z0-9\s-]/g, '')
                            .trim()
                            .replace(/[\s-]+/g, '-');
                    };

                    nameInput.addEventListener('input', updateSlug);
                    updateSlug();
                });
            </script>
            HTML);
    }

    public function units(int $productId): JsonResponse
    {
        $product = Product::query()->findOrFail($productId);

        return response()->json([
            'units' => $product->units()->latest()->get()->map(fn (ProductUnit $unit) => $this->unitSummary($unit)),
        ]);
    }

    public function storeUnit(ProductUnitRequest $request, int $productId): JsonResponse
    {
        $product = Product::query()->findOrFail($productId);
        $validated = $request->validated();

        if ((int) $validated['product_id'] !== $productId) {
            return response()->json(['message' => 'The selected product does not match this page.'], 422);
        }

        $photos = collect($request->file('photos', []))
            ->map(fn ($photo) => $photo->store('product-units', 'public'))
            ->all();
        $attributes = collect($validated)->except(['product_id', 'photos'])->all();
        $attributes['photos'] = $photos ?: null;

        $unit = $product->units()->create($attributes);

        return response()->json(['unit' => $this->unitSummary($unit)], 201);
    }

    private function unitSummary(ProductUnit $unit): array
    {
        return [
            'sku' => $unit->sku,
            'website' => $unit->website,
            'storage' => $unit->storage,
            'color' => $unit->color,
            'condition' => ProductUnit::CONDITIONS[$unit->condition] ?? $unit->condition,
            'selling_price' => Naira::format($unit->selling_price),
            'status' => ProductUnit::STATUSES[$unit->status] ?? $unit->status,
        ];
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}