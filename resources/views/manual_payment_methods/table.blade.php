<div class="card-body">
    <table class="table mb-0" id="aiz-data-table">
        <thead>
            <tr>
                @if (auth()->user()->can('approve_offline_wallet_recharge'))
                <th>
                    <div class="form-group">
                        <div class="aiz-checkbox-inline">
                            <label class="aiz-checkbox pt-5px d-block">
                                <input type="checkbox" class="check-all">
                                <span class="aiz-square-check"></span>
                            </label>
                        </div>
                    </div>
                </th>
                @else
                <th class="">#</th>
                @endif
                <th class="text-uppercase fs-10 fs-md-12 fw-700 text-secondary">
                    {{ translate('Name') }}
                </th>
                <th class="hide-md text-uppercase fs-10 fs-md-12 fw-700 text-secondary ml-1 ml-lg-0">
                    {{ translate('Amount') }}
                </th>
                <th class="hide-3xl text-uppercase fs-10 fs-md-12 fw-700 text-secondary ml-1 ml-lg-0">
                    {{ translate('Payment Method') }}
                </th>
                <th class="hide-4xl text-uppercase fs-10 fs-md-12 fw-700 text-secondary ml-1 ml-lg-0">
                    {{ translate('TXN ID') }}
                </th>
                <th class="hide-xxl text-uppercase fs-10 fs-md-12 fw-700 text-secondary ml-1 ml-lg-0">
                    {{ translate('Receipt') }}
                </th>
                <th class="hide-sm text-uppercase fs-10 fs-md-12 fw-700 text-secondary ml-1 ml-lg-0">
                    {{ translate('Approval') }}
                </th>
                <th class="hide-xs text-uppercase fs-10 fs-md-12 fw-700 text-secondary ml-1 ml-lg-0">
                    {{ translate('Date') }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $key => $request)
            <tr class="data-row">
                <td class="align-middle h-40">
                    <div>
                        <button type="button"
                            class="toggle-plus-minus-btn border-0 bg-blue fs-14 fw-500 text-white p-0 align-items-center justify-content-center">+</button>
                    </div>
                    @if (auth()->user()->can('approve_offline_wallet_recharge'))
                    <div class="form-group d-inline-block">
                        <label class="aiz-checkbox">
                            <input type="checkbox" class="check-one" name="id[]"
                                value="{{ $request->id }}">
                            <span class="aiz-square-check"></span>
                        </label>
                    </div>
                    @else
                    <div class="form-group d-inline-block">
                        {{ $key + 1 + ($requests->currentPage() - 1) * $requests->perPage() }}
                    </div>
                    @endif
                </td>
                <td class="" data-label="Name">
                    <div class="row gutters-5 w-200px w-md-200px mw-200">
                        <div class="col">
                            <span
                                class="text-dark fs-14 fw-700">
                                {{ $request->user->name }}
                            </span>
                        </div>
                    </div>
                </td>
                <td class="hide-md" data-label="Amount">
                    <div class="row gutters-5 w-200px w-md-200px mw-200">
                        <div class="col">
                            <span
                                class="text-dark fs-14 fw-300">{{ $request->amount }}</span>
                        </div>
                    </div>
                </td>
                <td class="hide-3xl" data-label="Payment Method">
                    <div class="row gutters-5 w-200px w-md-200px mw-200">
                        <div class="col">
                            @if ($request->payment_method != null)
                                <span class="text-dark fs-14 fw-300">{{ $request->payment_method }}</span>
                            @else
                                <span class="text-dark fs-14 fw-300"> -- </span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="hide-4xl" data-label="TXN ID">
                    <div class="row gutters-5 w-200px w-md-200px mw-200">
                        <div class="col">
                            <span
                                class="text-dark fs-14 fw-300">{{ $request->payment_details }}</span>
                        </div>
                    </div>
                </td>
                <td class="hide-xxl" data-label="Receipt">
                    <div class="row gutters-5 w-200px w-md-200px mw-200">
                        <div class="col">
                            @if ($request->reciept != null)
                                <span
                                    class="text-blue fs-14 fw-700">
                                        <a href="{{ uploaded_asset($request->reciept) }}"
                                            target="_blank">{{ translate('Open Reciept') }}</a>
                                </span>
                            @else
                                <span class="text-dark fs-14 fw-300"> -- </span>
                            @endif    
                        </div>
                    </div>
                </td>
                <td class="hide-sm" data-label="Approval">
                    <div class="row gutters-5 w-200px w-md-200px mw-200">
                        <div class="col">
                            <span
                                class="text-dark fs-14 fw-300">
                                @if ( $request->approval == 0 )
                                    <label class="aiz-switch aiz-switch-blue mb-0">
                                        <input
                                        @can('approve_offline_wallet_recharge') onchange="update_approved('{{ $request->id }}', this)" @endcan
                                        value="{{ $request->id }}" type="checkbox"
                                        @if ($request->approval == 1) checked @endif
                                        @cannot('approve_offline_wallet_recharge') disabled @endcan
                                        >
                                        <span class="slider round"></span>
                                    </label>
                                @else
                                    <span class="text-success fs-14 fw-300"><i class="las la-check-circle mr-1 fs-14"></i>{{translate('Approved')}}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </td>
                <td class="hide-xs" data-label="Date">
                    <div class="row gutters-5 w-200px w-md-200px mw-200">
                        <div class="col">
                            <span
                                class="text-dark fs-12 fw-700">
                                {{ $request->created_at }}
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="aiz-pagination">
        {{ $requests->appends(request()->input())->links() }}
    </div>
</div>