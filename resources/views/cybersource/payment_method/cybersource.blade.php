<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="cybersource">

   
    <div class="form-group row">
        <input type="hidden" name="types[]" value="CYBERSOURCE_SECRET_KEY">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('Cybersource Secret Key') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="CYBERSOURCE_SECRET_KEY"
                value="{{ env('CYBERSOURCE_SECRET_KEY') }}"
                placeholder="{{ translate('Cybersource Secret Key') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="CYBERSOURCE_ACCESS_KEY">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('Cybersource Access Key') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="CYBERSOURCE_ACCESS_KEY"
                value="{{ env('CYBERSOURCE_ACCESS_KEY') }}"
                placeholder="{{ translate('Cybersource Access Key') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="CYBERSOURCE_PROFILE_ID">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('Cybersource Profile Id') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="CYBERSOURCE_PROFILE_ID"
                value="{{ env('CYBERSOURCE_PROFILE_ID') }}"
                placeholder="{{ translate('Cybersource Profile Id') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="CYBERSOURCE_URL">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('Cybersource URL') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="CYBERSOURCE_URL"
                value="{{ env('CYBERSOURCE_URL') }}"
                placeholder="{{ translate('Cybersource URL') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('Cybersource Callbak URL') }}</label>
        </div>
        <div class="col-lg-8">
            <span>{{route('cybersource.callback')}}</span>
            {{-- <span>{{route('home').'/cyber-source/payment/callback'}}</span> --}}
        </div>
    </div>
    <div class="form-group row">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('Cybersource Webhook URL') }}</label>
        </div>
        <div class="col-lg-8">
            <span>{{route('cybersource.webhook')}}</span>
            {{-- <span>{{route('home').'/cyber-source/payment/webhook'}}</span> --}}
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Sandbox Mode') }}</label>
        </div>
        <div class="col-md-8">
            <label class="aiz-switch aiz-switch-success mb-0">
                <input value="1" name="cybersource_sandbox" type="checkbox" @if (get_setting('cybersource_sandbox') == 1) checked @endif>
                <span class="slider round"></span>
            </label>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>