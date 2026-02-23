<!-- Offcanvas Header -->
<div class="border-sm-bottom pb-15px px-30px">
    <div class="d-flex align-items-center justify-content-between">
        <h6 class="fs-16 fw-700 text-dark mb-0">
            {{ translate('Payment Information') }}
        </h6>
        <button onclick="closeOffcanvas()" class="border-0 bg-transparent">
            ✕
        </button>
    </div>
</div>

<!-- Offcanvas Body -->
<div class="right-offcanvas-body position-absolute h-100 px-30px pt-20px">

    @if ($paymentInfo->payment_type == 'others')

        <div class="form-group mb-3">
            <label class="fw-700">{{ translate('Name') }}</label>
            <input lang="en" class="form-control mb-3 rounded-0" placeholder="{{ translate('Name') }}" value="{{ $paymentInfo->payment_name }}" readonly>
        </div>

        <div class="form-group mb-3">
            <label class="fw-700">{{ translate('Payment Instructions') }}</label>
            <textarea lang="en" class="form-control mb-3 rounded-0" rows="3" placeholder="{{ translate('Account Name') }}" readonly>{{ $paymentInfo->payment_instruction }}</textarea>
        </div>

    @else
        <div class="form-group mb-3">
            <label class="fw-700">{{ translate('Bank Name') }}</label>
            <input lang="en" class="form-control mb-3 rounded-0" placeholder="{{ translate('Bank Name') }}" value="{{ $paymentInfo->bank_name }}" readonly>
        </div>

        <div class="form-group mb-3">
            <label class="fw-700">{{ translate('Account Name') }}</label>
            <input lang="en" class="form-control mb-3 rounded-0" placeholder="{{ translate('Account Name') }}" value="{{ $paymentInfo->account_name }}" readonly>
        </div>

        <div class="form-group mb-3">
            <label class="fw-700">{{ translate('Account Number') }}</label>
            <input lang="en" class="form-control mb-3 rounded-0" placeholder="{{ translate('Account Number') }}" value="{{ $paymentInfo->account_number }}" readonly>
        </div>

        <div class="form-group mb-3">
            <label class="fw-700">{{ translate('Routing Number') }}</label>
            <input lang="en" class="form-control mb-3 rounded-0" placeholder="{{ translate('Routing Number') }}" value="{{ $paymentInfo->routing_number }}" readonly>
        </div>
    @endif
</div>
