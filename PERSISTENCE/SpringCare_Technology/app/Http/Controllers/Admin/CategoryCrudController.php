<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class CategoryCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use UpdateOperation;

    public function setup()
    {
        CRUD::setModel(Category::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/category');
        CRUD::setEntityNameStrings('category', 'categories');
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
        CRUD::column('slug')->type('text')->label('Slug');
        CRUD::column('is_active')->type('boolean')->label('Active');
        CRUD::column('sort_order')->type('number')->label('Display Order');
        $this->crud->removeButton('preview');
        $this->crud->removeButton('update');
        $this->crud->removeButton('revisions');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('show');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(CategoryRequest::class);
        CRUD::field('name')->type('text')->label('Name');
        CRUD::field('slug')->type('text')->label('Slug')->attributes([
            'readonly' => 'readonly',
            'style' => 'background-color: var(--tblr-bg-surface-secondary, #e9ecef); color: var(--tblr-secondary, #6c757d);',
        ]);
        CRUD::field('description')->type('textarea')->label('Description');
        CRUD::field('is_active')->type('checkbox')->label('Active')->default(1);
        CRUD::field('sort_order')->type('number')->label('Display Order')
            ->default(0)->attributes(['min' => 0, 'step' => 1]);
        CRUD::field('slug_script')->type('custom_html')->value(<<<'HTML'
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

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}