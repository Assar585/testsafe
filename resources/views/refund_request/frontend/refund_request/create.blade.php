@extends('frontend.layouts.app')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="d-flex align-items-start">
                @include('frontend.inc.user_side_nav')
                <div class="aiz-user-panel">
                    <div class="card rounded-0 shadow-none border">
                        <div class="card-header border-bottom-0">
                            <h5 class="mb-0 fs-20 fw-700 text-dark">{{translate('Send Refund Request')}}</h5>
                        </div>
                        <div class="card-body">
                            <form id="aizSubmitForm" action="{{route('refund_request_send', $order_detail->id)}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="col-from-label">{{translate('Product Name')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-0" name="name" placeholder="{{translate('Product Name')}}" value="{{ $order_detail->product->getTranslation('name') }}" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label">{{translate('Amount')}} <span class="text-danger">*</span></label>
                                    @if(is_numeric($order_detail->gst_amount))
                                    <input type="number" class="form-control rounded-0" name="amount" placeholder="{{translate('Product Price')}}"  value="{{ round($order_detail->price + get_gst_by_price_and_rate($order_detail->price, $order_detail->gst_rate), 2) }}" readonly>
                                    @else
                                    <input type="number" class="form-control rounded-0" name="amount" placeholder="{{translate('Product Price')}}" value="{{ $order_detail->price + $order_detail->tax }}" readonly>
                                    @endif
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label">{{translate('Order Code')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-0" name="code" value="{{ $order_detail->order->code }}" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label">{{translate('Refund Reason')}} <span class="text-danger">*</span></label>
                                    <textarea name="reason" rows="5" class="form-control rounded-0" required></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label">{{ translate('Image') }}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium rounded-0">{{ translate('Browse')}}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="images" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                                @if (addon_is_activated('offline_payment') && addon_is_activated(identifier: 'refund_request'))
                                    <input type="hidden" name="payment_information_id" id="payment_information_id" value="{{ $payment_information_id }}">
                                    <div class="form-group">
                                        <label class="col-from-label">
                                            {{ translate('Preferred Channel') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <label class="aiz-megabox d-block bg-white mb-0 mr-4" style="flex: 1; min-width: 120px;"> 
                                                <input type="radio" name="preferred_payment_channel" value="wallet" checked>
                                                <span class="d-flex align-items-center aiz-megabox-elem rounded-0"
                                                    style="padding: 0.75rem 1.2rem;">
                                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                    <span class="flex-grow-1 pl-3 fw-600">{{ translate('Wallet') }}</span>
                                                </span>
                                            </label>
                                            <label class="aiz-megabox d-block bg-white mb-0" style="flex: 1; min-width: 120px;">
                                                <input type="radio" name="preferred_payment_channel" value="offline">
                                                <span class="d-flex align-items-center aiz-megabox-elem rounded-0"
                                                    style="padding: 0.75rem 1.2rem;">
                                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                    <span class="flex-grow-1 pl-3 fw-600">{{ translate('Offline') }}</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group mt-3 mb-3" id="paymentInformationSection" style="display: none;">
                                        <label class="col-from-label">
                                            {{translate('Select Payment Information')}} <span class="text-danger">*</span>
                                        </label>
                                        <div class="card mb-0 rounded-0 border shadow-none">
                                            <div id="collapsePaymentInformation" class="collapse show">
                                                <div class="card-body">
                                                    <div id="refund-payment-context">
                                                        @include('frontend.partials.payment_information.payment_info', ['payment_information_id' => $payment_information_id])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group mb-0 text-right ">
                                    <button type="submit" class="btn btn-primary rounded-0 w-150px">{{translate('Send Request')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('modal')
    <!-- Edit Payment Information Modal -->
    <div class="modal fade" id="edit-payment-information-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Edit Payment Information') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body c-scrollbar-light" id="edit_modal_body_payment">
                </div>
            </div>
        </div>
    </div>
    <!-- Payment Information Modal -->
    @if(Auth::check())
        @include('frontend.partials.payment_information.payment_information_modal')
    @endif
@endsection

@section('script')
    @include('frontend.partials.payment_information.payment_information_js')
    <script>
        $(document).ready(function () {
            $('#aizSubmitForm').on('submit', function () {
                let $btn = $(this).find('button[type="submit"]');

                $btn.prop('disabled', true);
                $btn.html(`
                    <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                    {{ translate('Sending...') }}
                `);
            });
        });
    </script>
@endsection
