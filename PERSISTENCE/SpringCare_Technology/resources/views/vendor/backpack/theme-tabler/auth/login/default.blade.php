@extends(backpack_view('layouts.auth'))

@section('before_styles')
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
@endsection

@section('content')
    <main class="sc-login-page">
        <section class="sc-login-brand" aria-label="SpringCare Technology">
            <div class="sc-login-wordmark">{!! backpack_theme_config('project_logo') !!}</div>

            <div class="sc-login-brand-copy">
                <p class="sc-login-eyebrow">SPRINGCARE ADMIN</p>
                <h1>Good to see<br>you again.</h1>
                <p>Your workspace is ready when you are.</p>
            </div>

            <p class="sc-login-brand-footer">Care, connected.</p>
        </section>

        <section class="sc-login-panel">
            <div class="sc-login-form">
                <p class="sc-login-eyebrow">ACCOUNT ACCESS</p>
                @include(backpack_view('auth.login.inc.form'))
                <p class="sc-login-note">Authorized team members only</p>
            </div>
        </section>
    </main>
@endsection