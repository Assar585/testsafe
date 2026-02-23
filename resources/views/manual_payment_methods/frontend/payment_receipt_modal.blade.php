@if ($receipt)
    <div class="row">
        <div class="col-md-12 px-4">
            <img src="{{ uploaded_asset($receipt) }}" class="w-100 mb-4">
        </div>
    </div>
@else
    <p class="text-muted">{{ translate('No receipt found') }}</p>
@endif
