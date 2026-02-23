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
                    <h1 class="h3 fw-bold">{{ translate('Offline Wallet Recharge Requests') }}</h1>
                </div>
            </div>
        </div>
        <div class="card">
            <!--Nav Tab -->
            <div class="d-flex align-items-center justify-content-between flex-wrap border-bottom  border-light px-25px">
                <div class="table-tabs-container">
                    @php
                        $active_tab = $active_tab ?? 'all-recharge-requests';
                    @endphp
                    <ul class="nav nav-tabs border-0 " id="myTab" role="tablist">
                        @foreach ($recharge_tabs as $recharge_tab)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-0 pb-15px fs-14 fw-500 {{ $active_tab == Str::slug($recharge_tab) ? 'active' : '' }}" data-toggle="tab"  role="tab" aria-selected="{{ $active_tab == Str::slug($recharge_tab) ? 'true' : 'false' }}"
                                id="{{ Str::slug($recharge_tab) }}-tab"  onclick="changeTab(this, '{{ Str::slug($recharge_tab) }}')" aria-controls="{{ Str::slug($recharge_tab) }}">
                                {{ translate($recharge_tab) }}
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!--Card Header (Search) Start-->
            <div class="tab-filter-bar">
                <form class="" id="sort_requests" action="" method="GET">
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

                        <div class="dropdown mb-2 mb-md-0 bg-light mt-2 mt-md-0 px-md-1 rounded-1">
                            <button class="btn border dropdown-toggle border-light text-secondary fs-14 fw-400" type="button"
                                data-toggle="dropdown">
                                {{ translate('Bulk Action') }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                @can('approve_offline_wallet_recharge')
                                    <a class="dropdown-item confirm-alert text-success fs-14 fw-500 hov-bg-light hov-text-blue"
                                        href="javascript:void(0)" onclick="bulkApproved()">
                                        {{ translate('Approved') }}
                                    </a>
                                @endcan
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
@section('script')
    <script type="text/javascript">
        let currentTab = '{{ $active_tab }}';
        var searchTimer;

        $(document).on("change", ".check-all", function() 
        {
            if(this.checked) {                                               
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });
        function sort_requests(el)
        {
            $('#sort_requests').submit();
        }
        
        function getRequests(slug, page = 1) 
        {
            var status = $('#status').val();
            var user_id = $('#user_id').val();
            currentTab = slug;
            var slug = slug.replace(/-/g, '_');
            let keyword = $('#search_input').val();
            $('#tab-content').html('<div class="footable-loader mt-5"><span class="fooicon fooicon-loader"></span></div>');
            $.ajax({
                url: `{{ route('requests.filter') }}?page=${page}`,
                method: 'GET',
                data: { status: status, request_status: slug, search: keyword },
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
            getRequests(statusSlug);
        }

        document.addEventListener('DOMContentLoaded', function() 
        {
            getRequests(currentTab);
        });

        $('#search_input').on('keyup', function () 
        {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                getRequests(currentTab);
            }, 500);
        });

        $(document).on('click', '.pagination a', function(e) 
        {
            e.preventDefault();
            const page = $(this).attr('href').split('page=')[1];
            getRequests(currentTab, page);
        });

        let lastCheckbox = null;
        function update_approved(request_id, checkbox) 
        {
            lastCheckbox = checkbox;

            showBulkActionModal();
            $('#confirmation-title').text('{{ translate('Approve This Wallet Recharge Request !') }}');
            $('#confirmation-question').text('{{ translate('Do you want to approve this Wallet Recharge request?') }}');
            $('#conform-yes-btn').attr("onclick", "approve_request(" + request_id + ")");
            $('.confirmation-icon').addClass('d-none');
            $('#approve-confirm-icon').removeClass('d-none');
        }

        function approve_request(request_id) 
        {
            lastCheckbox = null;
            hideBulkActionModal();
            $.ajax({
                url: "{{ route('offline_recharge_request.approved', ':id') }}".replace(':id', request_id),
                type: 'POST',
                data: {
                    _token: AIZ.data.csrf,
                    request_id: request_id
                },
                success: function(response) {
                    if (response == 1) {
                        AIZ.plugins.notify('success', '{{ translate('Money has been added successfully.') }}');
                        getRequests(currentTab);
                    }
                }
            });
        }

        $('#bulk-action-modal').on('hidden.bs.modal', function () {
            if (lastCheckbox) {
                lastCheckbox.checked = false;
                lastCheckbox = null;
            }
        });

        function sort_requests(el) {
            $('#sort_requests').submit();
        }

        function bulkApproved() 
        {
            if ($('.check-one:checked').length == 0) {
                AIZ.plugins.notify('danger', '{{ translate('Please select at least one wallet recharge request') }}');
                return;
            }

            showBulkActionModal();
            $('#confirmation-title').text('{{ translate('Approve Confirmation') }}');
            $('#confirmation-question').text('{{ translate('Are you sure you want to approve the selected wallet recharge requests?') }}');
            $('#conform-yes-btn').attr("onclick","bulk_approve()");
            $('.confirmation-icon').addClass('d-none');
            $('#approve-confirm-icon').removeClass('d-none');
            
        }

        function bulk_approve() 
        {
            var data = new FormData($('#sort_requests')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-wallet-requests-approve')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        AIZ.plugins.notify('success', 'Selected wallet recharge requests approved successfully');
                        hideBulkActionModal();
                        getRequests(currentTab);
                    }
                },
                error: function () {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection
