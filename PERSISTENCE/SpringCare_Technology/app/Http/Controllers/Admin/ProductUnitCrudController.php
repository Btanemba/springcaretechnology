<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductUnitRequest;
use App\Models\Product;
use App\Models\ProductUnit;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ProductUnitCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use UpdateOperation;

    public function setup()
    {
        CRUD::setModel(ProductUnit::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/product-unit');
        CRUD::setEntityNameStrings('product unit', 'product units');
    }

    protected function setupListOperation()
    {
        CRUD::setResponsiveTable(false);
        CRUD::addColumn([
            'name' => 'custom_actions',
            'type' => 'view',
            'view' => 'vendor.backpack.crud.columns.custom_button',
            'orderable' => false,
            'searchable' => false,
            'visibleInExport' => false,
        ]);
        CRUD::column('sku')->type('text')->label('SKU');
        CRUD::column('product_id')->type('select')->entity('product')
            ->model(Product::class)->attribute('name')->label('Product');
        CRUD::addColumn([
            'name' => 'website',
            'type' => 'view',
            'view' => 'vendor.backpack.crud.columns.external_link',
            'label' => 'Original Website',
        ]);
        CRUD::column('storage')->type('text')->label('Storage');
        CRUD::column('color')->type('text')->label('Color');
        CRUD::column('condition')->type('select_from_array')->options(ProductUnit::CONDITIONS)->label('Condition');
        CRUD::column('battery_health')->type('number')->suffix('%')->label('Battery Health');
        CRUD::addColumn([
            'name' => 'selling_price',
            'type' => 'view',
            'view' => 'vendor.backpack.crud.columns.naira',
            'label' => 'Selling Price',
        ]);
        CRUD::column('status')->type('select_from_array')->options(ProductUnit::STATUSES)->label('Status');
        $this->crud->removeButton('preview');
        $this->crud->removeButton('update');
        $this->crud->removeButton('revisions');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('show');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductUnitRequest::class);
        CRUD::field('product_id')->type('select')->entity('product')
            ->model(Product::class)->attribute('name')->label('Product')->tab('Device');
        CRUD::field('sku')->type('text')->label('SKU')->hint('Optional. Enter N/A or Unavailable if you prefer a value.')->tab('Device');
        CRUD::field('serial_number')->type('text')->label('Serial Number')->tab('Device');
        CRUD::field('website')->type('url')->label('Original Website')->hint('Where this individual device came from.')->tab('Device');
        CRUD::field('storage')->type('text')->label('Storage')->tab('Device');
        CRUD::field('color')->type('text')->label('Color')->tab('Device');
        CRUD::field('description')->type('textarea')->label('Description')->tab('Details');
        CRUD::field('specifications')->type('textarea')->label('Specifications')->tab('Details')
            ->attributes(['rows' => 6]);
        CRUD::field('condition')->type('select_from_array')->options(ProductUnit::CONDITIONS)
            ->allows_null(false)->label('Condition')->tab('Condition');
        CRUD::field('battery_health')->type('number')->label('Battery Health (%)')->tab('Condition')
            ->attributes(['min' => 0, 'max' => 100, 'step' => 1]);
        CRUD::field('defects')->type('textarea')->label('Defects')->tab('Condition');
        CRUD::field('included_accessories')->type('textarea')->label('Included Accessories')->tab('Condition');
        CRUD::field('selling_price')->type('text')->label('Selling Price')->tab('Pricing')
            ->attributes(['inputmode' => 'decimal', 'autocomplete' => 'off', 'data-price-format' => 'true'])
            ->hint('Enter an amount in naira, for example 50,000.');
        CRUD::field('price_formatter')->type('custom_html')->tab('Pricing')
            ->value('<script src="'.asset('js/price-format.js').'" defer></script>');
        CRUD::field('status')->type('select_from_array')->options(ProductUnit::STATUSES)
            ->default('available')->allows_null(false)->label('Status')->tab('Pricing');
        CRUD::field('photos')->type('upload_multiple')->label('Photos')->tab('Photos')
            ->withFiles(['disk' => 'public', 'path' => 'product-units'])
            ->attributes(['accept' => 'image/jpeg,image/png,image/webp']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}