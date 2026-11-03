@php
    $id = $entry->getKey();
@endphp

@if ($id)
    @if ($crud->hasAccess('update', $entry))
        <a href="{{ url($crud->route . '/' . $id . '/edit') }}"
           class="btn btn-sm btn-primary"
           aria-label="{{ trans('backpack::crud.edit') }}">
            <i class="la la-pencil"></i>
        </a>
    @endif

    @include('crud::buttons.delete', ['crudTableId' => request()->input('datatable_id', 'crudTable')])
@else
    <span>-</span>
@endif
