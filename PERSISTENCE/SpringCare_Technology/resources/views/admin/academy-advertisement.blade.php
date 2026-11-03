@extends(backpack_view('blank'))

@section('header')
    <section class="container-fluid"><h2>Academy advert</h2></section>
@endsection

@section('content')
    <div class="row"><div class="col-lg-8">
        @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="card" method="POST" action="{{ route('admin.academy-advertisement.update') }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <h3 class="card-title">SpringCare Academy daycare</h3>
                <p class="text-muted">Homepage visibility: <strong>{{ $visible ? 'Visible now' : 'Hidden now' }}</strong></p>
                <div class="form-check form-switch mb-4">
                    <input type="hidden" name="enabled" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="enabled" name="enabled" value="1" @checked(old('enabled', $settings['enabled']))>
                    <label class="form-check-label" for="enabled">Enable advert</label>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label" for="starts_at">Show from (optional)</label><input class="form-control" id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $settings['starts_at']) }}" step="60">@error('starts_at')<div class="text-danger mt-1">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6 mb-3"><label class="form-label" for="ends_at">Hide at (optional)</label><input class="form-control" id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $settings['ends_at']) }}" step="60">@error('ends_at')<div class="text-danger mt-1">{{ $message }}</div>@enderror</div>
                </div>
                <p class="text-muted mb-0">Times are in {{ $timezone }} (WAT). Leave both dates empty for immediate, manual control. Disabling the advert overrides its schedule.</p>
            </div>
            <div class="card-footer d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit"><i class="la la-save" aria-hidden="true"></i> Save settings</button><a class="btn btn-secondary" href="{{ route('storefront.home') }}" target="_blank" rel="noopener noreferrer"><i class="la la-external-link-alt" aria-hidden="true"></i> View homepage</a></div>
        </form>
    </div></div>
@endsection