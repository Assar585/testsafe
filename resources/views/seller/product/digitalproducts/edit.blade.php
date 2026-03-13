@extends('seller.layouts.app')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 fw-700">{{ translate('Edit Digital Product') }}</h1>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('seller.digitalproducts.index') }}" class="btn btn-link text-reset">
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

    <form class="" action="{{ route('seller.digitalproducts.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="aizSubmitForm">
        <input name="_method" type="hidden" value="PATCH">
        <input type="hidden" name="id" value="{{ $product->id }}">
        <input type="hidden" name="lang" value="{{ $lang }}">
        @csrf

        <div class="row gutters-5">
            <div class="col-12">
                <div class="card mb-4">
                    <ul class="nav nav-tabs nav-fill language-bar">
                        @foreach (get_all_active_language() as $key => $language)
                            <li class="nav-item">
                                <a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3" 
                                   href="{{ route('seller.digitalproducts.edit', ['id' => $product->id, 'lang' => $language->code]) }}">
                                    <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                                    <span>{{$language->name}}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Product Information -->
                    <div class="bg-white p-3 p-sm-2rem">
                        <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                            {{translate('Product Information')}}
                        </h5>
                        <div class="row">
                            <div class="col-xxl-7 col-xl-6">
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">
                                        {{translate('Product Name')}} 
                                        <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="name" 
                                           placeholder="{{translate('Product Name')}}" 
                                           value="{{ $product->getTranslation('name', $lang) }}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Product File')}}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse')}}
                                            </div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="file_name" class="selected-files" value="{{ $product->file_name }}">
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{translate('Tags')}}</label>
                                    <input type="text" class="form-control aiz-tag-input" name="tags[]" id="tags" 
                                           value="{{ $product->tags }}" placeholder="{{ translate('Type and hit enter') }}">
                                    <small class="text-muted">{{translate('This is used for search. Input those words by which customer can find this product.')}}</small>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{ translate('Slug') }}</label>
                                    <input type="text" placeholder="{{ translate('Slug') }}" id="slug" name="slug" value="{{ $product->slug }}" class="form-control">
                                </div>
                            </div>

                            <div class="col-xxl-5 col-xl-6 mt-4 mt-xl-0">
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{translate('Product Category')}} <span class="text-danger">*</span></label>
                                    <div class="w-100 border p-3" style="max-height: 250px; overflow-y: auto;">
                                        <ul id="treeview" class="hummingbird-base">
                                            @foreach ($categories as $category)
                                                <li>
                                                    <i class="las la-plus"></i>
                                                    <label>
                                                        <input id="category-{{ $category->id }}" name="category_id"
                                                            type="radio" value="{{ $category->id }}" 
                                                            @if($product->category_id == $category->id) checked @endif required>
                                                        {{ $category->getTranslation('name') }}
                                                    </label>
                                                    @if (count($category->childrenCategories) > 0)
                                                        <ul>
                                                            @foreach ($category->childrenCategories as $childCategory)
                                                                @include('seller.product.products.child_category', [
                                                                    'child_category' => $childCategory,
                                                                    'product' => $product
                                                                ])
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                
                                @if (addon_is_activated('gst_system'))
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{translate('TN VED (HS Code)')}}</label>
                                    <select class="form-control aiz-selectpicker" name="hsn_code" id="hsn_code_select" data-live-search="true">
                                        @if($product->hsn_code)
                                            <option value="{{ $product->hsn_code }}" selected>{{ $product->hsn_code }}</option>
                                        @else
                                            <option value="">{{ translate('Select Code') }}</option>
                                        @endif
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
                        <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                            {{translate('Product Description')}}
                            <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i>
                        </h5>
                        <div class="w-100">
                            <div class="form-group mb-0">
                                <textarea class="aiz-text-editor" name="description">{{ $product->getTranslation('description', $lang) }}</textarea>
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
                                    <input type="hidden" name="photos" value="{{ $product->photos }}" class="selected-files">
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
                                    <input type="hidden" name="thumbnail_img" value="{{ $product->thumbnail_img }}" class="selected-files">
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
                                        <input type="number" lang="en" min="0" value="{{ $product->unit_price }}" step="0.01" placeholder="{{translate('Unit price')}}" name="unit_price" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @php
                                        $start_date = $product->discount_start_date ? date('d-m-Y H:i:s', $product->discount_start_date) : null;
                                        $end_date   = $product->discount_end_date ? date('d-m-Y H:i:s', $product->discount_end_date) : null;
                                    @endphp
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{ translate('Discount Date Range') }} </label>
                                        <input type="text" class="form-control aiz-date-range" 
                                               value="{{ $start_date && $end_date ? $start_date . ' to ' . $end_date : '' }}" 
                                               name="date_range" placeholder="{{ translate('Select Date') }}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="col-from-label fs-13">{{translate('Discount')}} <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-7">
                                            <input type="number" lang="en" min="0" value="{{ $product->discount }}" step="0.01" placeholder="{{translate('Discount')}}" name="discount" class="form-control" required>
                                        </div>
                                        <div class="col-5">
                                            <select class="form-control aiz-selectpicker" name="discount_type" required>
                                                <option value="amount" @if($product->discount_type == 'amount') selected @endif>{{translate('Flat')}}</option>
                                                <option value="percent" @if($product->discount_type == 'percent') selected @endif>{{translate('Percent')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (addon_is_activated('gst_system'))
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{translate('GST Rate (%)')}}</label>
                                    <input type="number" lang="en" min="0" value="{{ $product->gst_rate }}" step="0.01" placeholder="{{ translate('GST Rate') }}" name="gst_rate" class="form-control">
                                </div>
                            @else
                                <div class="w-100 border-top mt-3 pt-3">
                                    <h6 class="fs-14 fw-700 mb-3">{{ translate('VAT & Tax') }}</h6>
                                    @foreach (\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                                        @php
                                            $tax_amount = 0;
                                            $tax_type = '';
                                            foreach ($tax->product_taxes as $row) {
                                                if ($product->id == $row->product_id) {
                                                    $tax_amount = $row->tax;
                                                    $tax_type = $row->tax_type;
                                                }
                                            }
                                        @endphp
                                        <div class="form-group mb-3">
                                            <label class="col-from-label fs-13">{{ $tax->name }}</label>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <input type="hidden" value="{{$tax->id}}" name="tax_id[]">
                                                    <input type="number" lang="en" min="0" value="{{ $tax_amount }}" step="0.01" placeholder="{{translate('Tax')}}" name="tax[]" class="form-control" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-control aiz-selectpicker" name="tax_type[]">
                                                        <option value="amount" @if($tax_type == 'amount') selected @endif>{{translate('Flat')}}</option>
                                                        <option value="percent" @if($tax_type == 'percent') selected @endif>{{translate('Percent')}}</option>
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
                                    <input type="text" class="form-control" name="meta_title" value="{{ $product->meta_title }}" placeholder="{{translate('Meta Title')}}">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{translate('Description')}}</label>
                                    <textarea name="meta_description" rows="5" class="form-control">{{ $product->meta_description }}</textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Keywords') }}</label>
                                    <textarea class="resize-off form-control" name="meta_keywords" placeholder="{{translate('Keyword, Keyword')}}">{{ $product->meta_keywords }}</textarea>
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
                                        <input type="hidden" name="meta_img" value="{{ $product->meta_img }}" class="selected-files">
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
                                        <input id="fq_bought_select_products" type="radio" 
                                               name="frequently_bought_selection_type" value="product" 
                                               onchange="fq_bought_product_selection_type()" 
                                               @if($product->frequently_bought_selection_type == 'product') checked @endif>
                                        <label for="fq_bought_select_products" class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Product')}}</label>
                                    </div>
                                    <div class="radio mar-btm mr-3 d-flex align-items-center">
                                        <input id="fq_bought_select_category" type="radio" 
                                               name="frequently_bought_selection_type" value="category" 
                                               onchange="fq_bought_product_selection_type()" 
                                               @if($product->frequently_bought_selection_type == 'category') checked @endif>
                                        <label for="fq_bought_select_category" class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Category')}}</label>
                                    </div>
                                </div>

                                <div class="px-0">
                                    <div class="fq_bought_select_product_div d-none">
                                        @php 
                                            $fq_bought_products = $product->frequently_bought_products()->where('category_id', null)->get();
                                        @endphp
                                        <div id="selected-fq-bought-products">
                                            @if(count($fq_bought_products) > 0)
                                                @foreach($fq_bought_products as $fQBproduct)
                                                    <div class="row gutters-5 selected-fq-bought-product-row mb-3 remove-parent">
                                                        <input type="hidden" name="fq_bought_product_ids[]" value="{{ $fQBproduct->frequently_bought_product->id }}">
                                                        <div class="col-auto">
                                                            <img src="{{ uploaded_asset($fQBproduct->frequently_bought_product->thumbnail_img) }}" class="size-60px img-fit">
                                                        </div>
                                                        <div class="col">
                                                            <div class="text-truncate-2">{{ $fQBproduct->frequently_bought_product->getTranslation('name') }}</div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
                                                                <i class="las la-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-block border border-dashed hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center" onclick="showFqBoughtProductModal()">
                                            <i class="las la-plus"></i>
                                            <span class="ml-2">{{ translate('Add More') }}</span>
                                        </button>
                                    </div>

                                    <div class="fq_bought_select_category_div d-none">
                                        @php 
                                            $fq_bought_product_category_id = $product->frequently_bought_products()->where('category_id','!=', null)->first(); 
                                            $fqCategory = $fq_bought_product_category_id != null ? $fq_bought_product_category_id->category_id : null;
                                        @endphp
                                        <div class="form-group mb-3">
                                            <label class="col-from-label fs-13">{{translate('Category')}}</label>
                                            <select class="form-control aiz-selectpicker" 
                                                    data-placeholder="{{ translate('Select a Category')}}" 
                                                    name="fq_bought_product_category_id" data-live-search="true">
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected($fqCategory == $category->id)>{{ $category->getTranslation('name') }}</option>
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
                    <button type="submit" name="button" value="publish" class="btn btn-primary btn-lg min-w-150px fs-15 fw-700 shadow-primary">{{ translate('Update Product') }}</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('modal')
    <!-- Frequently Bought Product Select Modal -->
    @include('modals.product_select_modal')
@endsection

@section('script')
    <!-- Treeview js -->
    <script src="{{ static_asset('assets/js/hummingbird-treeview.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            AIZ.plugins.tagify();
            $("#treeview").hummingbird();
            fq_bought_product_selection_type();

            $('#treeview input:checkbox').on("click", function () {
                let $this = $(this);
                if ($this.prop('checked') && ($('#treeview input:radio:checked').length == 0)) {
                    let val = $this.val();
                    $('#treeview input:radio[value=' + val + ']').prop('checked', true);
                }
            });

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

        function fq_bought_product_selection_type(){
            var productSelectionType = $("input[name='frequently_bought_selection_type']:checked").val();
            if(productSelectionType == 'product'){
                $('.fq_bought_select_product_div').removeClass('d-none');
                $('.fq_bought_select_category_div').addClass('d-none');
            }
            else if(productSelectionType == 'category'){
                $('.fq_bought_select_category_div').removeClass('d-none');
                $('.fq_bought_select_product_div').addClass('d-none');
            }
        }

        function showFqBoughtProductModal() {
            $('#fq-bought-product-select-modal').modal('show', {backdrop: 'static'});
        }

        function filterFqBoughtProduct() {
            var productID = $('input[name=id]').val();
            var searchKey = $('input[name=search_keyword]').val();
            var fqBroughCategory = $('select[name=fq_brough_category]').val();
            $.post('{{ route('seller.product.search') }}', { _token: AIZ.data.csrf, product_id: productID, search_key:searchKey, category:fqBroughCategory, product_type:"digital" }, function(data){
                $('#product-list').html(data);
                AIZ.plugins.sectionFooTable('#product-list');
            });
        }

        function addFqBoughtProduct() {
            var selectedProducts = [];
            $("input:checkbox[name=fq_bought_product_id]:checked").each(function() {
                selectedProducts.push($(this).val());
            });

            var fqBoughtProductIds = [];
            $("input[name='fq_bought_product_ids[]']").each(function() {
                fqBoughtProductIds.push($(this).val());
            });

            var productIds = selectedProducts.concat(fqBoughtProductIds.filter((item) => selectedProducts.indexOf(item) < 0))

            $.post('{{ route('seller.get-selected-products') }}', { _token: AIZ.data.csrf, product_ids:productIds}, function(data){
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



