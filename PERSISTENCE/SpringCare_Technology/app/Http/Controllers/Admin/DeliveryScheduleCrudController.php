<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliveryScheduleRequest;
use App\Models\DeliverySchedule;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class DeliveryScheduleCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use UpdateOperation;

    public function setup(): void
    {
        CRUD::setModel(DeliverySchedule::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/delivery-schedule');
        CRUD::setEntityNameStrings('delivery schedule', 'delivery schedules');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('city')->type('text')->label('City');
        CRUD::column('month')->type('select_from_array')->options(DeliverySchedule::MONTHS)->label('Delivery Month');
        CRUD::column('year')->type('number')->label('Year');
        CRUD::column('is_published')->type('boolean')->label('Visible on Storefront');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(DeliveryScheduleRequest::class);
        CRUD::field('city')->type('text')->label('City');
        CRUD::field('month')->type('select_from_array')->options(DeliverySchedule::MONTHS)
            ->allows_null(false)->label('Delivery Month');
        CRUD::field('year')->type('number')->label('Year')->default(now()->year)
            ->attributes(['min' => 2000, 'max' => now()->year + 10, 'step' => 1]);
        CRUD::field('is_published')->type('checkbox')->label('Show on Storefront')->default(1);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}