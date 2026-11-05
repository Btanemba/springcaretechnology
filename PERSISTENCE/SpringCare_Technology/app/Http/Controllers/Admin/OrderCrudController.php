<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class OrderCrudController extends CrudController
{
    use ListOperation;
    use UpdateOperation;

    public function setup(): void
    {
        CRUD::setModel(Order::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/order');
        CRUD::setEntityNameStrings('order', 'orders');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn([
            'name' => 'order_actions',
            'type' => 'view',
            'view' => 'vendor.backpack.crud.columns.order_actions',
            'orderable' => false,
            'searchable' => false,
            'visibleInExport' => false,
            'label' => 'Actions',
        ]);
        CRUD::column('order_number')->type('text')->label('Order');
        CRUD::column('first_name')->type('text')->label('First name');
        CRUD::column('last_name')->type('text')->label('Last name');
        CRUD::column('email')->type('text')->label('Email');
        CRUD::column('delivery_city')->type('text')->label('Delivery location');
        CRUD::column('state')->type('text')->label('State');
        CRUD::column('status')->type('select_from_array')->options(Order::STATUSES)->label('Order status');
        CRUD::column('payment_status')->type('select_from_array')->options(Order::PAYMENT_STATUSES)->label('Payment');
        CRUD::column('subtotal')->type('number')->label('Subtotal');
        $this->crud->removeButton('update');
    }

    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(OrderRequest::class);
        foreach (['order_number', 'first_name', 'last_name', 'email', 'phone', 'state', 'delivery_address', 'delivery_city', 'delivery_month', 'delivery_year', 'subtotal'] as $field) {
            CRUD::field($field)->type('text')->attributes(['readonly' => 'readonly']);
        }
        CRUD::field('status')->type('select_from_array')->options(Order::STATUSES)->label('Order status');
        CRUD::field('payment_status')->type('select_from_array')->options(Order::PAYMENT_STATUSES)->label('Payment status');
        CRUD::field('payment_reference')->type('text')->label('Bank transfer reference');
    }
}