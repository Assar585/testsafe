@extends('seller.layouts.app')

@section('panel_content')

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
  <div class="modal fade reject_refund_request" id="modal-basic">
    	<div class="modal-dialog">
    		<div class="modal-content">
            <form class="form-horizontal member-block" action="{{ route('seller.reject_refund_request')}}" method="POST">
                @csrf
                <input type="hidden" name="refund_id" id="refund_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title h6">{{translate('Reject Refund Request !')}}</h5>
                    <button type="button" class="close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Reject Reason')}}</label>
                        <div class="col-md-9">
                            <textarea type="text" name="reject_reason" rows="5" class="form-control" placeholder="{{translate('Reject Reason')}}" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-success">{{translate('Submit')}}</button>
                </div>
            </form>
      	</div>
    	</div>
    </div>
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

@endsection

@section('script')
    <script type="text/javascript">

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
                url: `{{ route('seller.refund_requests.filter') }}?page=${page}`,
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

        function update_refund_approval(el){
            $.post('{{ route('seller.vendor_refund_approval') }}',{_token:'{{ @csrf_token() }}', el:el}, function(data){
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Approval has been done successfully') }}');
                }
                else {
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function reject_refund_request(id) {
           $('.reject_refund_request').modal('show');
           $('#refund_id').val(id);
        }

        function refund_reject_reason_show(url){
            $.get(url, function(data){
                 $('.reject_reason_show').html(data);
                 $('.reject_reason_show_modal').modal('show');
            });
        }
    </script>
@endsection