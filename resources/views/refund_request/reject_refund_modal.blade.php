<!-- Offcanvas Header -->
<div class="border-sm-bottom pb-15px px-30px">
    <div class="d-flex align-items-center justify-content-between">
        <h6 class="fs-16 fw-700 text-dark mb-0">
            {{ translate('Reject Offline Refund Request !') }}
        </h6>
        <button onclick="closeOffcanvas()" class="border-0 bg-transparent">
            ✕
        </button>
    </div>
</div>

<!-- Offcanvas Body -->
<div class="right-offcanvas-body position-absolute h-100 px-30px pt-20px">

    <div class="form-group mb-3">
        <label class="fw-700">{{ translate('Order Code') }}</label>
        <input lang="en" class="form-control mb-3 rounded-0" placeholder="{{ translate('Order Code') }}" value="{{ $order_code }}" readonly>
    </div>

    <div class="form-group mb-3">
        <label class="fw-700">{{ translate('Reject Reason') }}</label>
        <textarea type="text" class="form-control mb-3 rounded-0" rows="5" name="reject_reason" placeholder="{{ translate('Reject Reason') }}" required></textarea>
    </div>


</div>

<!-- Offcanvas Footer -->
<div class="w-100 px-30px position-absolute bottom-0 bg-white right-offcavas-footer pt-20px pb-20px">
    <div class="d-flex justify-content-end">
        <button type="button"
            class="fs-14 fw-700 py-10px px-20px btn btn-primary"
            id="reject-refund-request"
            data-refund-id="{{ $refund_id }}">
            {{ translate('Confirm') }}
        </button>
    </div>
</div>
