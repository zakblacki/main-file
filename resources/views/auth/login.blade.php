@extends('layouts.auth')
@section('page-title')
    {{ __('Login') }}
@endsection
@section('language-bar')
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ Str::upper($lang) }}
                </span>
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach (languages() as $key => $language)
                    <a href="{{ route('login', $key) }}"
                        class="dropdown-item @if ($lang == $key) text-primary @endif">
                        <span>{{ Str::ucfirst($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection
@php
    $admin_settings = getAdminAllSetting();
@endphp

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="">
                <h2 class="mb-3 f-w-600">{{ __('Login') }}</h2>
            </div>
            <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate="" id="form_data">
                @csrf
                <div>
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input id="email" type="email" class="form-control  @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" placeholder="{{ __('E-Mail Address') }}" required
                            autofocus>
                        @error('email')
                            <span class="error invalid-email text-danger" role="alert">
                                <small>{{ $message }}</small>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Password') }}</label>
                        <input id="password" type="password" class="form-control  @error('password') is-invalid @enderror"
                            name="password" placeholder="{{ __('Password') }}" required>
                        @error('password')
                            <span class="error invalid-password text-danger" role="alert">
                                <small>{{ $message }}</small>
                            </span>
                        @enderror
                        @if (Route::has('password.request'))
                            <div class="mt-2">
                                <a href="{{ route('password.request', $lang) }}"
                                    class="small text-primary text-underline--dashed border-primar">{{ __('Forgot Your Password?') }}</a>
                            </div>
                        @endif
                    </div>
                    @stack('recaptcha_field')

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block mt-2 login_button"
                            tabindex="4">{{ __('Login') }}</button>

                        @stack('SigninButton')
                    </div>
                    @if (empty($admin_settings['signup']) || (isset($admin_settings['signup']) ? $admin_settings['signup'] : 'off') == 'on')
                        <p class="my-3 text-center">{{ __("Don't have an account?") }}
                            <a href="{{ route('register', $lang) }}" class="my-4 text-primary">{{ __('Register') }}</a>
                        </p>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $("#form_data").submit(function(e) {
                $(".login_button").attr("disabled", true);
                setInterval(() => {
                    $(".login_button").attr("disabled", false);
                }, 1500);
            });
        });
    </script>
@endpush
