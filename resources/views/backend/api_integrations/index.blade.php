@extends('backend.layouts.app')

@section('title', translate('API & Integrations'))

@section('content')
    <style>
        .integration-card {
            border: 1px solid #f1f1f4;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 12px rgba(35, 39, 52, .06);
            transition: box-shadow .2s, border-color .2s;
            overflow: hidden;
        }

        .integration-card:hover {
            box-shadow: 0 6px 24px rgba(35, 39, 52, .12);
            border-color: #d5d6e0;
        }

        .integration-card .card-header-custom {
            padding: 16px 20px 12px;
            border-bottom: 1px solid #f1f1f4;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .service-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f4effe, #e8f4fd);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .integration-card .card-body-custom {
            padding: 16px 20px 20px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot.active {
            background: #19c553;
        }

        .status-dot.inactive {
            background: #adb5bd;
        }

        .key-field-wrap {
            position: relative;
        }

        .key-field-wrap .toggle-vis {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #adb5bd;
            font-size: 14px;
        }

        .tab-category-btn {
            border: none;
            background: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #6c757d;
            cursor: pointer;
            transition: background .15s, color .15s;
            white-space: nowrap;
        }

        .tab-category-btn.active,
        .tab-category-btn:hover {
            background: #009ef7;
            color: #fff;
        }

        .category-pane {
            display: none;
        }

        .category-pane.active {
            display: block;
        }

        .add-new-btn {
            border: 2px dashed #d5d6e0;
            border-radius: 12px;
            background: transparent;
            cursor: pointer;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #adb5bd;
            transition: border-color .2s, color .2s;
            width: 100%;
        }

        .add-new-btn:hover {
            border-color: #009ef7;
            color: #009ef7;
        }
    </style>

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h3 mb-0">
                    <i class="las la-plug text-primary mr-2"></i>
                    {{ translate('API & Integrations') }}
                </h1>
                <p class="text-muted fs-12 mb-0 mt-1">
                    {{ translate('Manage all external service connections from one place') }}
                </p>
            </div>
        </div>
    </div>

    @php
        $categories = [
            'logistics' => [
                'icon' => '🚚',
                'label' => translate('Logistics & Couriers'),
                'services' => [
                    ['name' => 'dhl', 'label' => 'DHL Express', 'url' => 'https://api.dhl.com'],
                    ['name' => 'fedex', 'label' => 'FedEx API', 'url' => 'https://apis.fedex.com'],
                    ['name' => 'cdek', 'label' => 'СДЭК', 'url' => 'https://api.cdek.ru/v2'],
                    ['name' => 'boxberry', 'label' => 'Boxberry', 'url' => 'https://api.boxberry.ru'],
                    ['name' => 'pochta', 'label' => 'Почта России', 'url' => 'https://otpravka-api.pochta.ru'],
                ]
            ],
            'insurance' => [
                'icon' => '🛡️',
                'label' => translate('Insurance'),
                'services' => [
                    ['name' => 'sogaz', 'label' => 'Согаз', 'url' => ''],
                    ['name' => 'ingosstrakh', 'label' => 'Ингосстрах', 'url' => ''],
                    ['name' => 'sberins', 'label' => 'СберСтрахование', 'url' => ''],
                ]
            ],
            'banking' => [
                'icon' => '🏦',
                'label' => translate('Banking & Payments'),
                'services' => [
                    ['name' => 'sber', 'label' => 'Сбербанк', 'url' => 'https://api.sberbank.ru'],
                    ['name' => 'tbank', 'label' => 'Т-Банк', 'url' => 'https://securepay.tinkoff.ru/v2'],
                    ['name' => 'paypal', 'label' => 'PayPal', 'url' => 'https://api-m.paypal.com'],
                    ['name' => 'stripe', 'label' => 'Stripe', 'url' => 'https://api.stripe.com/v1'],
                    ['name' => 'alfa', 'label' => 'Альфа-Банк', 'url' => ''],
                ]
            ],
            'ai' => [
                'icon' => '🤖',
                'label' => translate('AI Services'),
                'services' => [
                    ['name' => 'openai', 'label' => 'OpenAI (ChatGPT)', 'url' => 'https://api.openai.com/v1'],
                    ['name' => 'gemini', 'label' => 'Google Gemini', 'url' => 'https://generativelanguage.googleapis.com'],
                    ['name' => 'claude', 'label' => 'Anthropic Claude', 'url' => 'https://api.anthropic.com'],
                ]
            ],
            'customs' => [
                'icon' => '🌍',
                'label' => translate('Customs & Trade'),
                'services' => [
                    ['name' => 'fts', 'label' => 'ФТС России (ЕТС)', 'url' => ''],
                    ['name' => 'tsouz', 'label' => 'Таможенный союз API', 'url' => ''],
                ]
            ],
            'analytics' => [
                'icon' => '📊',
                'label' => translate('Analytics'),
                'services' => [
                    ['name' => 'google_analytics', 'label' => 'Google Analytics 4', 'url' => ''],
                    ['name' => 'metrika', 'label' => 'Яндекс Метрика', 'url' => 'https://api-metrika.yandex.net'],
                ]
            ],
        ];
    @endphp

    {{-- Category Tab Buttons --}}
    <div class="d-flex flex-wrap gap-2 mb-4" style="gap:8px;">
        @foreach($categories as $catKey => $cat)
            <button class="tab-category-btn {{ $loop->first ? 'active' : '' }}" onclick="switchTab('{{ $catKey }}', this)">
                {{ $cat['icon'] }} {{ $cat['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Category Panes --}}
    @foreach($categories as $catKey => $cat)
        <div class="category-pane {{ $loop->first ? 'active' : '' }}" id="tab-{{ $catKey }}">
            <div class="row">
                @foreach($cat['services'] as $svc)
                    @php
                        $integration = $integrations->get($catKey)?->firstWhere('service_name', $svc['name']);
                    @endphp
                    <div class="col-md-6 col-xl-4 mb-4">
                        <div class="integration-card h-100">
                            <div class="card-header-custom">
                                <div class="service-icon">{{ $cat['icon'] }}</div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-600 fs-14">{{ $svc['label'] }}</span>
                                        <span class="status-dot {{ $integration?->is_active ? 'active' : 'inactive' }}"></span>
                                    </div>
                                    <small class="text-muted">{{ ucfirst($catKey) }}</small>
                                </div>
                                {{-- Toggle --}}
                                <div class="ml-auto">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" onchange="toggleIntegration({{ $integration?->id ?? 'null' }}, this)"
                                            {{ $integration?->is_active ? 'checked' : '' }} {{ !$integration ? 'disabled' : '' }}>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="card-body-custom">
                                <form action="{{ route('admin.api_integrations.store') }}" method="POST" class="needs-validation"
                                    novalidate>
                                    @csrf
                                    <input type="hidden" name="category" value="{{ $catKey }}">
                                    <input type="hidden" name="service_name" value="{{ $svc['name'] }}">
                                    <input type="hidden" name="label" value="{{ $svc['label'] }}">

                                    {{-- API Key --}}
                                    <div class="form-group mb-2">
                                        <label class="fs-12 text-muted mb-1">{{ translate('API Key') }}</label>
                                        <div class="key-field-wrap">
                                            <input type="password" name="api_key" class="form-control form-control-sm pr-5"
                                                placeholder="{{ $integration?->api_key ? '••••••••' : translate('Enter API key') }}"
                                                autocomplete="new-password">
                                            <span class="toggle-vis" onclick="toggleVis(this)">
                                                <i class="las la-eye"></i>
                                            </span>
                                        </div>
                                        <small class="text-muted fs-11">{{ translate('Leave blank to keep existing key') }}</small>
                                    </div>

                                    {{-- API Secret --}}
                                    <div class="form-group mb-2">
                                        <label class="fs-12 text-muted mb-1">{{ translate('API Secret / Token') }}</label>
                                        <div class="key-field-wrap">
                                            <input type="password" name="api_secret" class="form-control form-control-sm pr-5"
                                                placeholder="{{ $integration?->api_secret ? '••••••••' : translate('Enter secret') }}"
                                                autocomplete="new-password">
                                            <span class="toggle-vis" onclick="toggleVis(this)">
                                                <i class="las la-eye"></i>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- API URL --}}
                                    <div class="form-group mb-3">
                                        <label class="fs-12 text-muted mb-1">{{ translate('API Endpoint URL') }}</label>
                                        <input type="url" name="api_url" class="form-control form-control-sm"
                                            value="{{ $integration?->api_url ?? $svc['url'] }}"
                                            placeholder="https://api.example.com">
                                    </div>

                                    <div class="d-flex align-items-center" style="gap:8px;">
                                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                            <i class="las la-save mr-1"></i>{{ translate('Save') }}
                                        </button>
                                        @if($integration)
                                            <button type="button" class="btn btn-soft-info btn-sm"
                                                title="{{ translate('Test Connection') }}"
                                                onclick="testConnection({{ $integration->id }}, this)">
                                                <i class="las la-plug"></i>
                                            </button>
                                            <form action="{{ route('admin.api_integrations.destroy', $integration->id) }}" method="POST"
                                                class="m-0" onsubmit="return confirm('{{ translate('Remove this integration?') }}')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-soft-danger btn-sm"
                                                    title="{{ translate('Remove') }}">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </form>

                                {{-- Docs link --}}
                                @if(!empty($svc['url']))
                                    <div class="mt-2 text-right">
                                        <a href="{{ $svc['url'] }}" target="_blank" class="fs-11 text-muted">
                                            <i class="las la-external-link-alt"></i> {{ translate('API Docs') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Add Custom Integration Card --}}
                <div class="col-md-6 col-xl-4 mb-4">
                    <button class="add-new-btn" data-toggle="modal" data-target="#addCustomModal" data-category="{{ $catKey }}">
                        <i class="las la-plus-circle" style="font-size:32px;"></i>
                        <span class="fs-13 fw-500">{{ translate('Add Custom Integration') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Add Custom Modal --}}
    <div class="modal fade" id="addCustomModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i
                            class="las la-plug text-primary mr-1"></i>{{ translate('Add Custom Integration') }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('admin.api_integrations.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="category" id="custom-category">
                        <div class="form-group">
                            <label>{{ translate('Service Name (unique ID)') }} <span class="text-danger">*</span></label>
                            <input type="text" name="service_name" class="form-control" placeholder="my_service" required
                                pattern="[a-z0-9_]+"
                                title="{{ translate('Lowercase letters, numbers and underscore only') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Display Label') }}</label>
                            <input type="text" name="label" class="form-control"
                                placeholder="{{ translate('My Service') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('API Key') }}</label>
                            <input type="text" name="api_key" class="form-control" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('API Secret / Token') }}</label>
                            <input type="text" name="api_secret" class="form-control" autocomplete="off">
                        </div>
                        <div class="form-group mb-0">
                            <label>{{ translate('API Endpoint URL') }}</label>
                            <input type="url" name="api_url" class="form-control" placeholder="https://api.example.com">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('Save Integration') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function switchTab(cat, btn) {
            document.querySelectorAll('.category-pane').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab-category-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + cat)?.classList.add('active');
            btn.classList.add('active');
        }

        function toggleVis(el) {
            const input = el.closest('.key-field-wrap').querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                el.innerHTML = '<i class="las la-eye-slash"></i>';
            } else {
                input.type = 'password';
                el.innerHTML = '<i class="las la-eye"></i>';
            }
        }

        function toggleIntegration(id, el) {
            if (!id) { el.checked = !el.checked; return; }
            $.post('{{ route("admin.api_integrations.toggle", "__ID__") }}'.replace('__ID__', id), {
                _token: '{{ csrf_token() }}'
            }, function (res) {
                AIZ.plugins.notify(res.status ? 'success' : 'info',
                    res.status ? '{{ translate("Integration activated") }}' : '{{ translate("Integration deactivated") }}');
            }).fail(function () {
                el.checked = !el.checked;
                AIZ.plugins.notify('danger', '{{ translate("Error toggling status") }}');
            });
        }

        function testConnection(id, btn) {
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="las la-spinner la-spin"></i>';
            $.get('{{ route("admin.api_integrations.test", "__ID__") }}'.replace('__ID__', id), function (res) {
                AIZ.plugins.notify(res.success ? 'success' : 'danger', res.message);
            }).always(function () {
                btn.disabled = false;
                btn.innerHTML = orig;
            });
        }

        // Pass category to modal
        $('#addCustomModal').on('show.bs.modal', function (e) {
            const cat = $(e.relatedTarget).data('category');
            $('#custom-category').val(cat);
        });
    </script>
@endsection