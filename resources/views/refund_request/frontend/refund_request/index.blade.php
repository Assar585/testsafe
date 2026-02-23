@extends('frontend.layouts.app')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-start">
            @include('frontend.inc.user_side_nav')
            <div class="aiz-user-panel">
                <div class="card shadow-none rounded-0 border p-4">
                    <h5 class="mb-2 fs-20 fw-700 text-dark">{{ translate('Applied Refund Requests') }}</h5>
                    <hr>
                    <div class="card-body py-0 pt-3 px-0">
                        <div class="mb-4">
                            <div class="row align-items-center mb-3">
                                <div class="col-md-12">
                                    @foreach($refunds as $key => $refund)
                                    <div class="row">
                                        <div class="col-md-3 col-xl-4 d-flex align-items-center mb-1 mb-md-0">
                                            <div class="border rounded-0 mr-3 ">
                                                <img src="{{ uploaded_asset($refund->orderDetail->product->thumbnail_img) }}"
                                                    class="img-fit product-history-img w-30px h-30px w-sm-50px h-sm-50px w-md-48px h-md-48px overflow-hidden">
                                            </div>
                                            <div class="w-100 text-wrap">
                                                <div class="font-weight-semibold fs-14 product-name-color text-truncate-2"
                                                    title="{{ $refund->orderDetail->product->getTranslation('name') }}">
                                                    {{ $refund->orderDetail->product->getTranslation('name') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-xl-3">
                                            <div>
                                                <span class="text-muted">{{ translate('Applied') }}:</span> 
                                                <span class="fw-bold">{{ date('d-m-Y', strtotime($refund->created_at)) }}</span>
                                            </div>
                                            <div class="text-muted">{{ translate('Order Code') }}</div>
                                            <div class="font-weight-bold"> <a class="text-blue" href="{{route('purchase_history.details', encrypt($refund->order->id))}}">{{$refund->order->code}}</a></div>
                                        </div>
                                        <div class="col-md-2 col-xl-2">
                                            <div class="text-muted">{{ translate('Amount') }}</div>
                                            <div class="font-weight-bold">{{single_price($refund->refund_amount)}}</div>
                                        </div>
                                        <div class="col-md-3 col-lg-2 col-xl-1">
                                            <div class="text-muted">{{ translate('Channel') }}</div>
                                            <div class="font-weight-bold">{{ ucfirst($refund->preferred_payment_channel) }}</div>
                                        </div>
                                        <div class="col-md-3 col-lg-2 mt-1 mt-lg-0">
                                            <div class="row">
                                                @if ($refund->refund_status == 1)
                                                    @if ($refund->preferred_payment_channel == 'offline' && $refund->photo != null)
                                                        <div class="col-md-12 text-right">
                                                            <div class="d-inline-block dropdown ml-1">
                                                                <button type="button"
                                                                    class="btn btn-success btn-sm dropdown-toggle text-white px-3 py-1 rounded-1 w-100px"
                                                                    data-toggle="dropdown">
                                                                    {{ translate('Approved') }}
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-right ">
                                                                    <a class="dropdown-item text-secondary dropdown-bg-hover" href="javascript:void(0)" onclick="showReceipt({{ $refund->id }})"><i class="las la-eye mr-2"></i>{{ translate('Open Receipt') }}</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-md-12 text-right">
                                                            <div class="d-inline-block dropdown ml-1">
                                                                <button type="button"
                                                                    class="btn btn-success btn-sm text-white px-3 py-1 rounded-0 w-100px"
                                                                    data-toggle="dropdown">
                                                                    {{ translate('Approved') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @elseif ($refund->refund_status == 2)    
                                                    <div class="col-md-12 text-right">
                                                        <div class="d-inline-block dropdown ml-1">
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm dropdown-toggle text-white px-3 py-1 rounded-1 w-100px"
                                                                data-toggle="dropdown">
                                                                {{ translate('Rejected') }}
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right ">
                                                                <a class="dropdown-item text-secondary dropdown-bg-hover" href="javascript:void(0)" onclick="refund_reject_reason_show('{{ route('reject_reason_show', $refund->id )}}')"><i class="las la-eye mr-2"></i>{{ translate('Reject Reason') }}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="col-md-12 text-right">
                                                        <div class="d-inline-block dropdown ml-1">
                                                            <button type="button"
                                                                class="btn btn-info btn-sm text-white px-3 py-1 rounded-0 w-100px"
                                                                data-toggle="dropdown">
                                                                {{ translate('Pending') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif    
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ $refunds->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('modal')
<div class="modal fade reject_reason_show_modal" id="modal-basic">
	<div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title h6">{{translate('Refund Request Reject Reason')}}</h5>
              <button type="button" class="close" data-dismiss="modal"></button>
          </div>
          <div class="modal-body reject_reason_show">
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-sm btn-secondary rounded-0" data-dismiss="modal">{{translate('Close')}}</button>
          </div>
      </div>
	</div>
</div>

@include('refund_request.frontend.refund_request.receipt')
@endsection

@section('script')
<script type="text/javascript">
  function refund_reject_reason_show(url){
      $.get(url, function(data){
          $('.reject_reason_show').html(data);
          $('.reject_reason_show_modal').modal('show');
      });
  }

    function showReceipt(id){
        $('#receipt-show-modal .modal-body').html('');
        $.ajax({
            type: "GET",
            url: "{{ route('receipt.show', '') }}/"+id,
            data: {},
            success: function(data) {
                $('#receipt-show-modal .modal-body').html(data);
                $('#receipt-show-modal').modal('show');
            }
        });
    }
</script>
@endsection
