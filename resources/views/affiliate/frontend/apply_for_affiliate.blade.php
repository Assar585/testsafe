@extends('frontend.layouts.app')

@section('content')
    <section class="pt-4 mb-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 text-center text-lg-left">
                    <h3 class="fw-600 h4">{{ translate('Affiliate Informations') }}</h3>
                </div>
                <div class="col-lg-6">
                    <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                        <li class="breadcrumb-item opacity-50">
                            <a class="text-reset" href="{{ route('home') }}">{{ translate('Home') }}</a>
                        </li>
                        <li class="text-dark fw-600 breadcrumb-item">
                            <a class="text-reset" href="{{ route('affiliate.apply') }}">"{{ translate('Affiliate') }}"</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-8 mx-auto">
                    <form id="affiliate-apply-form" class="" action="{{ route('affiliate.store_affiliate_user') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if (!Auth::check())
                            <div class="card rounded-0 shadow-none">
                                <div class="card-header border-bottom-0">
                                    <h5 class="mb-0 fs-15 fw-600">{{translate('User Info')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <div class="input-group input-group--style-1">
                                                    <input type="text" class="form-control rounded-0{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name') }}" placeholder="{{ translate('Name') }}" name="name">
                                                    <span class="input-group-addon">
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <div class="input-group input-group--style-1">
                                                    <input type="email" class="form-control rounded-0{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ translate('Email') }}" name="email">
                                                    <span class="input-group-addon">
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <div class="input-group input-group--style-1">
                                                    <input type="password" class="form-control rounded-0{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ translate('Password') }}" name="password">
                                                    <span class="input-group-addon">
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <div class="input-group input-group--style-1">
                                                    <input type="password" class="form-control rounded-0" placeholder="{{ translate('Confirm Password') }}" name="password_confirmation">
                                                    <span class="input-group-addon">
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="card rounded-0 shadow-none">
                            <div class="card-header border-bottom-0">
                                <h5 class="mb-0 fs-15 fw-600">{{translate('Verification info')}}</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $verification_form = get_Affiliate_onfig_value();
                                @endphp
                                    @foreach (json_decode($verification_form) as $key => $element)
                                        @if ($element->type == 'text')
                                            <div class="row">
                                                <label class="col-md-2 col-form-label">{{ $element->label }} <span class="text-danger">*</span></label>
                                                <div class="col-md-10">
                                                    <input type="{{ $element->type }}" class="form-control rounded-0 mb-3" placeholder="{{ $element->label }}" name="element_{{ $key }}" required>
                                                </div>
                                            </div>
                                        @elseif($element->type == 'file')
                                            <div class="row">
                                                <label class="col-md-2 col-form-label">{{ $element->label }}</label>
                                                <div class="col-md-10">
                                                    <input type="{{ $element->type }}" name="element_{{ $key }}" id="file-{{ $key }}" class="custom-input-file custom-input-file--4" data-multiple-caption="{count} files selected" required/>
                                                    <label for="file-{{ $key }}" class="mw-100 mb-3">
                                                        <span></span>
                                                        <strong>
                                                            <i class="fa fa-upload"></i>
                                                            {{translate('Choose file')}}
                                                        </strong>
                                                    </label>
                                                </div>
                                            </div>
                                        @elseif ($element->type == 'select' && is_array(json_decode($element->options)))
                                            <div class="row">
                                                <label class="col-md-2 col-form-label">{{ $element->label }}</label>
                                                <div class="col-md-10">
                                                    <div class="mb-3">
                                                        <select class="form-control rounded-0 selectpicker" data-minimum-results-for-search="Infinity" name="element_{{ $key }}" required>
                                                            @foreach (json_decode($element->options) as $value)
                                                                <option value="{{ $value }}">{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($element->type == 'multi_select' && is_array(json_decode($element->options)))
                                            <div class="row">
                                                <label class="col-md-2 col-form-label">{{ $element->label }}</label>
                                                <div class="col-md-10">
                                                    <div class="mb-3">
                                                        <select class="form-control rounded-0 selectpicker" data-minimum-results-for-search="Infinity" name="element_{{ $key }}[]" multiple required>
                                                            @foreach (json_decode($element->options) as $value)
                                                                <option value="{{ $value }}">{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($element->type == 'radio')
                                            <div class="row">
                                                <label class="col-md-2 col-form-label">{{ $element->label }}</label>
                                                <div class="col-md-10">
                                                    <div class="mb-3">
                                                        @foreach (json_decode($element->options) as $value)
                                                            <div class="radio radio-inline">
                                                                <input type="radio" name="element_{{ $key }}" value="{{ $value }}" id="{{ $value }}" required>
                                                                <label for="{{ $value }}">{{ $value }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                            </div>
                             <!-- Recaptcha -->
                            @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_affiliate_apply') == 1)
                                
                                @if ($errors->has('g-recaptcha-response'))
                                <div class="d-flex justify-content-center align-items-center">

                                    <span class="border invalid-feedback w-50 rounded p-2 mb-3 bg-danger text-white text-center" role="alert" style="display: block;">
                                        <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                    </span>
                                </div>
                                @endif
                            @endif
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary rounded-0 w-150px">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>


@endsection
@section('script')


    @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_affiliate_apply') == 1)
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('CAPTCHA_KEY') }}"></script>
        
        <script type="text/javascript">
                document.getElementById('affiliate-apply-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    grecaptcha.ready(function() {
                        grecaptcha.execute(`{{ env('CAPTCHA_KEY') }}`, {action: 'register'}).then(function(token) {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'hidden');
                            input.setAttribute('name', 'g-recaptcha-response');
                            input.setAttribute('value', token);
                            e.target.appendChild(input);

                            var actionInput = document.createElement('input');
                            actionInput.setAttribute('type', 'hidden');
                            actionInput.setAttribute('name', 'recaptcha_action');
                            actionInput.setAttribute('value', 'recaptcha_affiliate_apply');
                            e.target.appendChild(actionInput);
                            
                            e.target.submit();
                        });
                    });
                });
        </script>
    @endif
@endsection