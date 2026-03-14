@extends('seller.layouts.app')

@section('panel_content')

    <div class="page-content mx-0">
        <div class="aiz-titlebar mt-2 mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 fw-700">{{ translate('Add New Digital Product') }}</h1>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('seller.digitalproducts') }}" class="btn btn-link text-reset">
                        <i class="las la-angle-left"></i>
                        <span>{{translate('Back to product list')}}</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="" action="{{route('seller.digitalproducts.store')}}" method="POST" enctype="multipart/form-data" id="aizSubmitForm">
            @csrf
            <input type="hidden" name="added_by" value="seller">
            <input type="hidden" name="digital" value="1">

            <div class="row gutters-5">
                <div class="col-12">
                    <!-- Product Information -->
                    <div class="card mb-4" id="product_information">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{translate('Product Information')}}
                            </h5>
                            <div class="row">
                                <div class="col-xxl-7 col-xl-6">
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('Product Name')}} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" id="product_name" placeholder="{{translate('Product Name')}}" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{ translate('Product File')}} <span class="text-danger">*</span></label>
                                        <div class="input-group" data-toggle="aizuploader" data-multiple="false">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse')}}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="file_name" class="selected-files" required>
                                        </div>
                                        <div class="file-preview box sm"></div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('Tags')}}</label>
                                        <input type="text" class="form-control aiz-tag-input" name="tags[]" placeholder="{{ translate('Type and hit enter') }}">
                                        <small class="text-muted">{{translate('This is used for search. Input those words by which customer can find this product.')}}</small>
                                    </div>
                                </div>

                                <div class="col-xxl-5 col-xl-6 mt-4 mt-xl-0">
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('Product Category')}} <span class="text-danger">*</span></label>
                                        <input type="hidden" name="category_ids[]" id="category_ids_hidden" value="">
                                        <select class="form-control aiz-selectpicker @error('category_id') is-invalid @enderror" 
                                                name="category_id" id="category_id" data-live-search="true" required
                                                onchange="$('#category_ids_hidden').val(this.value)">
                                            <option value="">{{ translate('Select Category') }}</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ $category->getTranslation('name') }}
                                                </option>
                                                @foreach ($category->childrenCategories as $childCategory)
                                                    <option value="{{ $childCategory->id }}">
                                                        &nbsp;&nbsp;&nbsp;-- {{ $childCategory->getTranslation('name') }}
                                                    </option>
                                                    @foreach ($childCategory->childrenCategories as $subChildCategory)
                                                        <option value="{{ $subChildCategory->id }}">
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----
                                                            {{ $subChildCategory->getTranslation('name') }}
                                                        </option>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    @if (addon_is_activated('gst_system'))
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('TN VED (HS Code)')}}</label>
                                        <select class="form-control aiz-selectpicker" name="hsn_code" id="hsn_code_select" data-live-search="true">
                                            <option value="">{{ translate('Select Code') }}</option>
                                        </select>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Description -->
                    <div class="card mb-4" id="product_description">
                        <div class="bg-white p-3 p-sm-2rem">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0 fs-17 fw-700">
                                    {{translate('Product Description')}}
                                </h5>
                                @if(Route::has('seller.ai.generate'))
                                <button type="button" class="btn btn-sm btn-soft-primary"
                                    onclick="generateDescriptionAI()">
                                    <i class="las la-magic"></i> {{translate('Generate description by AI')}}
                                </button>
                                @endif
                            </div>
                            <div class="border-bottom border-gray border-dashed mb-3"></div>
                            <div class="w-100">
                                <div class="form-group mb-0">
                                    <textarea class="aiz-text-editor" id="product_description" name="description"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Files & Media -->
                    <div class="card mb-4" id="files_media">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{translate('Files & Media')}}
                            </h5>
                            <div class="w-100">
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{translate('Gallery Images')}} <small>(600x600)</small></label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse')}}
                                            </div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="photos" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{translate('Thumbnail Image')}} <small>(300x300)</small></label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse')}}
                                            </div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="thumbnail_img" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price & Tax -->
                    <div class="card mb-4" id="price_variation">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{translate('Price & Tax')}}
                            </h5>
                            <div class="w-100">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="col-from-label fs-13">{{translate('Unit price')}} <span class="text-danger">*</span></label>
                                            <input type="number" lang="en" min="0" value="0" step="0.01" placeholder="{{translate('Unit price')}}" name="unit_price" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="col-from-label fs-13">{{ translate('Discount Date Range') }} </label>
                                            <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="{{ translate('Select Date') }}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="col-from-label fs-13">{{translate('Discount')}} <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-7">
                                                <input type="number" lang="en" min="0" value="0" step="0.01" placeholder="{{translate('Discount')}}" name="discount" class="form-control" required>
                                            </div>
                                            <div class="col-5">
                                                <select class="form-control aiz-selectpicker" name="discount_type">
                                                    <option value="amount">{{translate('Flat')}}</option>
                                                    <option value="percent">{{translate('Percent')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if (addon_is_activated('gst_system'))
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('GST Rate (%)')}}</label>
                                        <input type="number" lang="en" min="0" value="0" step="0.01" placeholder="{{ translate('GST Rate') }}" name="gst_rate" class="form-control">
                                    </div>
                                @else
                                    <div class="w-100 border-top mt-3 pt-3">
                                        <h6 class="fs-14 fw-700 mb-3">{{ translate('VAT & Tax') }}</h6>
                                        @foreach (\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                                            <div class="form-group mb-3">
                                                <label class="col-from-label fs-13">{{ $tax->name }}</label>
                                                <div class="row">
                                                    <div class="col-md-7">
                                                        <input type="hidden" value="{{$tax->id}}" name="tax_id[]">
                                                        <input type="number" lang="en" min="0" value="0" step="0.01" placeholder="{{translate('Tax')}}" name="tax[]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-control aiz-selectpicker" name="tax_type[]">
                                                            <option value="amount">{{translate('Flat')}}</option>
                                                            <option value="percent">{{translate('Percent')}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Settings Toggle -->
                    <div class="mb-4">
                        <button class="btn btn-soft-secondary btn-block fs-14 fw-700 py-3" type="button" data-toggle="collapse" data-target="#advancedSettings" aria-expanded="false">
                            {{ translate('Advanced Settings') }}
                        </button>
                    </div>

                    <div class="collapse" id="advancedSettings">
                        <!-- SEO Meta Tags -->
                        <div class="card mb-4" id="seo_meta_tags">
                            <div class="bg-white p-3 p-sm-2rem">
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('SEO Meta Tags')}}
                                </h5>
                                <div class="w-100">
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('Meta Title')}}</label>
                                        <input type="text" class="form-control" name="meta_title" placeholder="{{translate('Meta Title')}}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('Description')}}</label>
                                        <textarea name="meta_description" rows="5" class="form-control"></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{ translate('Keywords') }}</label>
                                        <textarea class="resize-off form-control" name="meta_keywords" placeholder="{{translate('Keyword, Keyword')}}"></textarea>
                                        <small class="text-muted">{{ translate('Separate with coma') }}</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="col-from-label fs-13">{{ translate('Meta Image') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse')}}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="meta_img" class="selected-files">
                                        </div>
                                        <div class="file-preview box sm"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Frequently Bought Products -->
                        <div class="card mb-4" id="frequently_bought">
                            <div class="bg-white p-3 p-sm-2rem">
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{ translate('Frequently Bought') }}
                                </h5>
                                <div class="w-100">
                                    <div class="d-flex my-3">
                                        <div class="radio mar-btm mr-5 ml-4 d-flex align-items-center">
                                            <input id="fq_bought_select_products" type="radio" name="frequently_bought_selection_type" value="product" onchange="fq_bought_product_selection_type()" checked>
                                            <label for="fq_bought_select_products" class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Product')}}</label>
                                        </div>
                                        <div class="radio mar-btm mr-3 d-flex align-items-center">
                                            <input id="fq_bought_select_category" type="radio" name="frequently_bought_selection_type" value="category" onchange="fq_bought_product_selection_type()">
                                            <label for="fq_bought_select_category" class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Category')}}</label>
                                        </div>
                                    </div>

                                    <div class="px-0">
                                        <div class="fq_bought_select_product_div">
                                            <div id="selected-fq-bought-products"></div>
                                            <button type="button" class="btn btn-block border border-dashed hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center" onclick="showFqBoughtProductModal()">
                                                <i class="las la-plus"></i>
                                                <span class="ml-2">{{ translate('Add More') }}</span>
                                            </button>
                                        </div>

                                        <div class="fq_bought_select_category_div d-none">
                                            <div class="form-group mb-3">
                                                <label class="col-from-label fs-13">{{translate('Category')}}</label>
                                                <select class="form-control aiz-selectpicker" data-placeholder="{{ translate('Select a Category')}}" name="fq_bought_product_category_id" data-live-search="true">
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                                        @foreach ($category->childrenCategories as $childCategory)
                                                            @include('categories.child_category', ['child_category' => $childCategory])
                                                        @endforeach
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End Advanced Settings Collapse -->

                    <div class="col-12 mt-4 text-right">
                        <button type="submit" name="button" value="publish" class="btn btn-primary btn-lg min-w-150px fs-15 fw-700 shadow-primary">{{ translate('Upload Product') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@section('modal')
    <!-- Frequently Bought Product Select Modal -->
    @include('modals.product_select_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
            // $("#treeview").hummingbird(); // Removed fixed dropdown dropdown

            // Category selection removal if needed, but selectpicker handles it

            // HS Code Select2 AJAX autocomplete
            if ($('#hsn_code_select').length) {
                $('#hsn_code_select').select2({
                    placeholder: '{{ translate("Search by code or product name...") }}',
                    allowClear: true,
                    minimumInputLength: 0,
                    ajax: {
                        url: '{{ route("seller.products.hs_code_search") }}',
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return { q: params.term || '' };
                        },
                        processResults: function (data) {
                            return { results: data };
                        },
                        cache: true
                    }
                });
            }
        });

        // AI Generation Handler
        function generateDescriptionAI() {
            var productName = $('#product_name').val();
            var categoryId = $('#category_id').val();

            if (!productName || !categoryId) {
                AIZ.plugins.notify('danger', '{{ translate("Please enter a Product Name and select a Category first to generate an AI description.") }}');
                return;
            }

            let originalBtnHtml = $('button[onclick="generateDescriptionAI()"]').html();
            $('button[onclick="generateDescriptionAI()"]').html('<i class="las la-spinner la-spin"></i> {{ translate("Generating...") }}').prop('disabled', true);

            $.post('{{ route('seller.ai.generate') }}', {
                _token: '{{ csrf_token() }}',
                product_name: productName,
                category_id: categoryId
            }, function (data) {
                if (data.success) {
                    if ($('#product_description').length) {
                        $('#product_description').val(data.description);
                        if ($('#product_description').siblings('.note-editor').length) {
                            $('#product_description').siblings('.note-editor').find('.note-editable').html(data.description);
                        }
                    }
                    $('input[name="meta_title"]').val(data.meta_title);
                    $('textarea[name="meta_description"]').val(data.meta_description);

                    AIZ.plugins.notify('success', '{{ translate("AI Description & SEO Meta Tags generated successfully!") }}');
                } else {
                    AIZ.plugins.notify('danger', data.message || '{{ translate("Failed to generate description.") }}');
                }
            }).fail(function (xhr) {
                let errorMessage = '{{ translate("An error occurred during AI generation.") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                AIZ.plugins.notify('danger', errorMessage);
            }).always(function () {
                $('button[onclick="generateDescriptionAI()"]').html(originalBtnHtml).prop('disabled', false);
            });
        }

        function fq_bought_product_selection_type() {
            var productSelectionType = $("input[name='frequently_bought_selection_type']:checked").val();
            if (productSelectionType == 'product') {
                $('.fq_bought_select_product_div').removeClass('d-none');
                $('.fq_bought_select_category_div').addClass('d-none');
            }
            else if (productSelectionType == 'category') {
                $('.fq_bought_select_category_div').removeClass('d-none');
                $('.fq_bought_select_product_div').addClass('d-none');
            }
        }

        function showFqBoughtProductModal() {
            $('#fq-bought-product-select-modal').modal('show', { backdrop: 'static' });
        }

        function filterFqBoughtProduct() {
            var searchKey = $('input[name=search_keyword]').val();
            var fqBroughCategory = $('select[name=fq_brough_category]').val();
            $.post('{{ route('seller.product.search') }}', { _token: AIZ.data.csrf, product_id: null, search_key: searchKey, category: fqBroughCategory, product_type: "digital" }, function (data) {
                $('#product-list').html(data);
                AIZ.plugins.sectionFooTable('#product-list');
            });
        }

        function addFqBoughtProduct() {
            var selectedProducts = [];
            $("input:checkbox[name=fq_bought_product_id]:checked").each(function () {
                selectedProducts.push($(this).val());
            });

            var fqBoughtProductIds = [];
            $("input[name='fq_bought_product_ids[]']").each(function () {
                fqBoughtProductIds.push($(this).val());
            });

            var productIds = selectedProducts.concat(fqBoughtProductIds.filter((item) => selectedProducts.indexOf(item) < 0))

            $.post('{{ route('seller.get-selected-products') }}', { _token: AIZ.data.csrf, product_ids: productIds }, function (data) {
                $('#fq-bought-product-select-modal').modal('hide');
                $('#selected-fq-bought-products').html(data);
                AIZ.plugins.sectionFooTable('#selected-fq-bought-products');
            });
        }
    </script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    @include('partials.product.product_temp_data')
@endsection