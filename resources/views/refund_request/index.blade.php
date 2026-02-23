@extends('backend.layouts.app')

@section('content')
    @php
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
    @endphp


    <div class="col-12 col-sm-12 col-lg-12 mx-auto">
        <div class="aiz-titlebar text-left pb-5px">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h1 class="h3 fw-bold">{{ translate('All Refund Requests') }}</h1>
                </div>
            </div>
        </div>
        <div class="card">
            <!--Nav Tab -->
            <div class="d-flex align-items-center justify-content-between flex-wrap border-bottom  border-light px-25px">
                <div class="table-tabs-container">
                    @php
                        $active_tab = $active_tab ?? 'all-refunds';
                    @endphp
                    <ul class="nav nav-tabs border-0 " id="myTab" role="tablist">
                        @foreach ($refund_tabs as $refund_tab)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-0 pb-15px fs-14 fw-500 {{ $active_tab == Str::slug($refund_tab) ? 'active' : '' }}" data-toggle="tab"  role="tab" aria-selected="{{ $active_tab == Str::slug($refund_tab) ? 'true' : 'false' }}"
                                id="{{ Str::slug($refund_tab) }}-tab"  onclick="changeTab(this, '{{ Str::slug($refund_tab) }}')" aria-controls="{{ Str::slug($refund_tab) }}">
                                {{ translate($refund_tab) }}
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!--Card Header (Search) Start-->
            <div class="tab-filter-bar">
                <form class="" id="sort_refund_requests" action="" method="GET">
                    <div class="card-header row  border-0 pb-0 mt-2">
                        <div class="col pl-0 pl-md-3">
                            <div class="input-group mb-0 border border-light px-3 bg-light rounded-1">
                                <div class="input-group-prepend">
                                    <span class="input-group-text border-0 bg-transparent px-0" id="search">
                                        <svg id="Group_38844" data-name="Group 38844" xmlns="http://www.w3.org/2000/svg"
                                            width="16.001" height="16" viewBox="0 0 16.001 16">
                                            <path id="Path_3090" data-name="Path 3090"
                                                d="M8.248,14.642a6.394,6.394,0,1,1,6.394-6.394A6.4,6.4,0,0,1,8.248,14.642Zm0-11.509a5.115,5.115,0,1,0,5.115,5.115A5.121,5.121,0,0,0,8.248,3.133Z"
                                                transform="translate(-1.854 -1.854)" fill="#a5a5b8" />
                                            <path id="Path_3091" data-name="Path 3091"
                                                d="M23.011,23.651a.637.637,0,0,1-.452-.187l-4.92-4.92a.639.639,0,0,1,.9-.9l4.92,4.92a.639.639,0,0,1-.452,1.091Z"
                                                transform="translate(-7.651 -7.651)" fill="#a5a5b8" />
                                        </svg>
                                    </span>
                                </div>
                                <input type="text" class="form-control form-control-sm border-0 px-2 bg-transparent"
                                    id="search_input" name="search"placeholder="{{translate('Search Request ...')}}">
                            </div>
                        </div>
                    </div>
                    <!-- Dynamic Tab Content -->
                    <div class="tab-content filter-tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="tab-content">
                            <!-- AJAX content will load here -->
                        </div>
                    </div>
                </form>
            </div>
            <!--Card Header (Search) End-->
        </div>
    </div>
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
                <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Close')}}</button>
            </div>
        </div>
        </div>
    </div>

    <!-- Offcanvas -->
    <div id="rightOffcanvas" class="right-offcanvas-lg position-fixed top-0 fullscreen bg-white  py-20px z-1045">
        <!-- content will here -->
    </div>
    <!-- Overlay -->
    <div id="rightOffcanvasOverlay" class="position-fixed top-0 left-0 h-100 w-100"></div>

@endsection

@section('script')
    <script type="text/javascript">

        function refund_request_money(refund_id) 
        {
            showBulkActionModal();
            $('#confirmation-title').text('{{ translate('Approve Refund Request !') }}');
            $('#confirmation-question').text('{{ translate('Do you want to approve this refund request?') }}');
            $('#conform-yes-btn').attr("onclick", "approve_refund(" + refund_id + ")");
            $('.confirmation-icon').addClass('d-none');
            $('#approve-confirm-icon').removeClass('d-none');
        }

        function approve_refund(refund_id) 
        {
            hideBulkActionModal();
            $.ajax({
                url: "{{ route('refund_request_money_by_admin', ':id') }}".replace(':id', refund_id),
                type: 'POST',
                data: {
                    _token: AIZ.data.csrf,
                    refund_id: refund_id
                },
                success: function(response) {
                    if (response == 1) {
                        AIZ.plugins.notify('success', '{{ translate('Refund has been sent successfully.') }}');
                        getRefundRequests(currentTab);
                    }
                }
            });
        }

        function reject_refund_request(url, id, order_id){
          $.get(url, function(data){
              $('.reject_refund_request').modal('show');
              $('#refund_id').val(id);
              $('#order_id').val(order_id);
              $('#reject_reason').html(data);
          });
        }

        function refund_reject_reason_show(url){
            $.get(url, function(data){
                $('.reject_reason_show').html(data);
                $('.reject_reason_show_modal').modal('show');
            });
        }

        let currentTab = '{{ $active_tab }}';
        var searchTimer;

        $(document).on("change", ".check-all", function() 
        {
            if(this.checked) {
                // Iterate each checkbox                                                
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });
        function sort_refund_requests(el)
        {
            $('#sort_refund_requests').submit();
        }
        
        function getRefundRequests(slug, page = 1) 
        {
            var status = $('#status').val();
            var user_id = $('#user_id').val();
            currentTab = slug;
            var slug = slug.replace(/-/g, '_');
            let keyword = $('#search_input').val();
            $('#tab-content').html('<div class="footable-loader mt-5"><span class="fooicon fooicon-loader"></span></div>');
            $.ajax({
                url: `{{ route('refund_requests.filter') }}?page=${page}`,
                method: 'GET',
                data: { status: status, refund_request_status: slug, search: keyword },
                success: function(response) {
                    $('#tab-content').html(response.html);
                    initFooTable();
                },
                error: function() {
                    $('#tab-content').html('<div class="text-danger p-4">{{ translate("Failed to load data.") }}</div>');
                }
            });
        }

        function changeTab(button, statusSlug) 
        {
            document.querySelectorAll('#myTab .nav-link').forEach(el => el.classList.remove('active'));
            button.classList.add('active');
            getRefundRequests(statusSlug);
        }

        document.addEventListener('DOMContentLoaded', function() 
        {
            getRefundRequests(currentTab);
        });

        $('#search_input').on('keyup', function () 
        {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                getRefundRequests(currentTab);
            }, 500);
        });

        $(document).on('click', '.pagination a', function(e) 
        {
            e.preventDefault();
            const page = $(this).attr('href').split('page=')[1];
            getRefundRequests(currentTab, page);
        });

        const rightOffcanvas = document.getElementById('rightOffcanvas');
        const overlay = document.getElementById('rightOffcanvasOverlay');
        let selectedUserId = null;

        $(document).on('click', '#refund_request_money_by_offline', function (e) {
            e.preventDefault();

            selectedRefundId = $(this).data('user-id');
            openRightcanvas(selectedRefundId);
        });

        $(document).on('click', '#view_payment_info', function (e) {
            e.preventDefault();

            selectedRefundId = $(this).data('user-id');
            openRightcanvasPaymentInfo(selectedRefundId);
        });

        $(document).on('click', '#reject_refund_request', function (e) {
            e.preventDefault();

            selectedRefundId = $(this).data('refund-id');
            selectedOrderCode = $(this).data('order-code');

            openRightcanvasRejectRefund(selectedRefundId, selectedOrderCode);
        });

        function openRightcanvas(refundId) {

            rightOffcanvas.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('body-no-scroll');

            rightOffcanvas.innerHTML =
                '<div class="footable-loader mt-5"><span class="fooicon fooicon-loader"></span></div>';

            $.ajax({
                type: "POST",
                url: "{{ route('admin_offline_refund_request_modal') }}",
                data: {
                    _token: AIZ.data.csrf,
                    refund_id: refundId
                },
                success: function (html) {
                    rightOffcanvas.innerHTML = html;
                },
                error: function () {
                    rightOffcanvas.innerHTML =
                        '<p class="text-danger p-3">{{ translate("Failed to load") }}</p>';
                }
            });
        }

        function openRightcanvasPaymentInfo(refundId) {

            rightOffcanvas.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('body-no-scroll');

            rightOffcanvas.innerHTML =
                '<div class="footable-loader mt-5"><span class="fooicon fooicon-loader"></span></div>';

            $.ajax({
                type: "POST",
                url: "{{ route('view_payment_info_modal') }}",
                data: {
                    _token: AIZ.data.csrf,
                    refund_id: refundId
                },
                success: function (html) {
                    rightOffcanvas.innerHTML = html;
                },
                error: function () {
                    rightOffcanvas.innerHTML =
                        '<p class="text-danger p-3">{{ translate("Failed to load") }}</p>';
                }
            });
        }

        function openRightcanvasRejectRefund(refundId, orderCode) {

            rightOffcanvas.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('body-no-scroll');

            rightOffcanvas.innerHTML =
                '<div class="footable-loader mt-5"><span class="fooicon fooicon-loader"></span></div>';

            $.ajax({
                type: "POST",
                url: "{{ route('reject_refund_modal') }}",
                data: {
                    _token: AIZ.data.csrf,
                    refund_id: refundId,
                    order_code: orderCode
                },
                success: function (html) {
                    rightOffcanvas.innerHTML = html;
                },
                error: function () {
                    rightOffcanvas.innerHTML =
                        '<p class="text-danger p-3">{{ translate("Failed to load") }}</p>';
                }
            });
        }

        function closeRightcanvas() {
            rightOffcanvas.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('body-no-scroll');
        }

        function closeOffcanvas() {
            closeRightcanvas();
        }

        if (overlay) {
            overlay.addEventListener('click', closeRightcanvas);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeRightcanvas();
            }
        });
        
        $(document).on('click', '#confirm-refund-request', function () {
            const btn = $(this);
            const refund_id = btn.data('refund-id');
            const trx_id = $('input[name="trx_id"]').val();
            const photo = $('input[name="photo"]').val();

            if (!trx_id) {
                AIZ.plugins.notify('warning', 'Please fill transaction id');
                return;
            }

            btn.prop('disabled', true);
            if (!btn.find('.spinner-border').length) {
                btn.append('<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>');
            }

            $.ajax({
                url: "{{ route('refund_request_offline_money_by_admin') }}",
                type: "POST",
                data: {
                    _token: AIZ.data.csrf,
                    refund_id: refund_id,
                    trx_id: trx_id,
                    photo: photo
                },
                success: function (res) {
                    AIZ.plugins.notify('success', 'Refund has been sent successfully.');
                    closeRightcanvas();
                    location.reload();
                },
                error: function () {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                }
            });
        });
                
        $(document).on('click', '#reject-refund-request', function () {
            const btn = $(this);
            const refund_id = btn.data('refund-id');
            const reject_reason = $('textarea[name="reject_reason"]').val();

            if (!reject_reason) {
                AIZ.plugins.notify('warning', 'Please fill Reject Refund Reason');
                return;
            }

            btn.prop('disabled', true);
            if (!btn.find('.spinner-border').length) {
                btn.append('<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>');
            }

            $.ajax({
                url: "{{ route('admin.reject_refund_request') }}",
                type: "POST",
                data: {
                    _token: AIZ.data.csrf,
                    refund_id: refund_id,
                    reject_reason: reject_reason,
                },
                success: function (res) {
                    AIZ.plugins.notify('success', 'Refund has been rejected.');
                    closeRightcanvas();
                    location.reload();
                },
                error: function () {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                }
            });
        });
    </script>
@endsection