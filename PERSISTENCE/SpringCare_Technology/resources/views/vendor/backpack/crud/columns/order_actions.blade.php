@php($id = $entry->getKey())

@if ($id && $crud->hasAccess('update', $entry))
    <a href="{{ url($crud->route.'/'.$id.'/edit') }}"
       class="btn btn-sm btn-primary"
       aria-label="Edit order {{ $entry->order_number }}"
       title="Edit order">
        <i class="la la-pencil" aria-hidden="true"></i>
    </a>
@else
    <span>-</span>
@endif