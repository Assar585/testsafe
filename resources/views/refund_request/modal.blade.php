<!-- Offcanvas Header -->
<div class="border-sm-bottom pb-15px px-30px">
    <div class="d-flex align-items-center justify-content-between">
        <h6 class="fs-16 fw-700 text-dark mb-0">
            {{ translate('Approve Offline Refund Request !') }}
        </h6>
        <button onclick="closeOffcanvas()" class="border-0 bg-transparent">
            ✕
        </button>
    </div>
</div>

<!-- Offcanvas Body -->
<div class="right-offcanvas-body position-absolute h-100 px-30px pt-20px">
    @php
        $refund = App\Models\RefundRequest::findOrFail($refund_id);
        $refund_amount = $refund->refund_amount;
    @endphp

    <div class="form-group mb-3">
        <label class="fw-700">{{ translate('Amount') }}</label>
        <input type="number" lang="en" class="form-control mb-3 rounded-0" min="0" step="0.01" placeholder="{{ translate('Amount') }}" value="{{ $refund_amount }}" readonly>
    </div>

    <div class="form-group mb-3">
        <label class="fw-700">{{ translate('Transaction ID') }}</label>
        <input type="text" class="form-control mb-3 rounded-0" name="trx_id" placeholder="{{ translate('Transaction ID') }}" required>
    </div>

    <div class="form-group mb-3">
        <label class="fw-700">{{ translate('Photo') }}</label>
        <div class="input-group" data-toggle="aizuploader" data-type="image">
            <div class="input-group-prepend">
                <div class="input-group-text bg-soft-secondary font-weight-medium rounded-0">{{ translate('Browse')}}</div>
            </div>
            <div class="form-control file-amount">{{ translate('Choose image') }}</div>
            <input type="hidden" name="photo" class="selected-files">
        </div>
        <div class="file-preview box sm">
        </div>
    </div>

</div>

<!-- Offcanvas Footer -->
<div class="w-100 px-30px position-absolute bottom-0 bg-white right-offcavas-footer pt-20px pb-20px">
    <div class="d-flex justify-content-end">
        <button type="button"
            class="fs-14 fw-700 py-10px px-20px btn btn-primary"
            id="confirm-refund-request"
            data-refund-id="{{ $refund_id }}">
            {{ translate('Confirm') }}
        </button>
    </div>
</div>
