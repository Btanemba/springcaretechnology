<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class RoleCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use UpdateOperation;

    public function setup()
    {
        CRUD::setModel(Role::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/role');
        CRUD::setEntityNameStrings('role', 'roles');
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
        CRUD::column('code')->type('text')->label('Code');
         $this->crud->removeButton('preview');
        $this->crud->removeButton('update');
        $this->crud->removeButton('revisions');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('show');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(RoleRequest::class);
        CRUD::field('name')->type('text')->label('Name');
        CRUD::field('code')->type('text')->label('Code');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}