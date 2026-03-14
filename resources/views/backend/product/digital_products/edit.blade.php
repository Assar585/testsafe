@extends('backend.layouts.app')

@section('content')
    <div class="page-content mx-0">
        <div class="aiz-titlebar text-left mt-2 mb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3">{{ translate('Edit Digital Product') }}</h1>
                </div>
            </div>
        </div>

        <div class="">
            <!-- Error Meassages -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Data type -->
        <input type="hidden" id="data_type" value="digital">

        <form class="form form-horizontal mar-top" action="{{ route('digitalproducts.update', $product->id) }}"
            method="POST" enctype="multipart/form-data" id="choice_form">
            <div class="row gutters-5">
                <div class="col-lg-8">
                    <input name="_method" type="hidden" value="PATCH">
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    @csrf

                    <div class="card">
                        <ul class="nav nav-tabs nav-fill language-bar">
                            @foreach (get_all_active_language() as $key => $language)
                            <li class="nav-item">
                                <a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3" href="{{ route('digitalproducts.edit.admin', ['id' => $product->id, 'lang' => $language->code]) }}">
                                    <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                                    <span>{{$language->name}}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{ translate('General') }}
                            </h5>
                            <div class="w-100">
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Product Name') }} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="{{ translate('Product Name') }}"
                                        value="{{ $product->getTranslation('name', $lang) }}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Product File') }}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="file_name" class="selected-files"
                                            value="{{ $product->file_name }}">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{ translate('Tags') }}</label>
                                    <input type="text" class="form-control aiz-tag-input" name="tags[]" id="tags"
                                        value="{{ $product->tags }}" placeholder="{{ translate('Type to add a tag') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{ translate('Images') }}
                            </h5>
                            <div class="w-100">
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Main Images') }}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="photos" value="{{ $product->photos }}"
                                            class="selected-files" required>
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{ translate('Thumbnail Image') }}
                                        <small>(290x300)</small></label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="thumbnail_img" value="{{ $product->thumbnail_img }}"
                                            class="selected-files" required>
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{ translate('Meta Tags') }}
                            </h5>
                            <div class="w-100">
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Meta Title') }}</label>
                                    <input type="text" class="form-control" name="meta_title"
                                        value="{{ $product->meta_title }}" placeholder="{{ translate('Meta Title') }}">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Description') }}</label>
                                    <textarea name="meta_description" rows="5" class="form-control">{{ $product->meta_description }}</textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{translate('Keywords')}}</label>
                                    <textarea class="resize-off form-control" name="meta_keywords" placeholder="{{ translate('Keyword, Keyword') }}">{{ $product->meta_keywords }}</textarea>
                                    <small class="text-muted">{{ translate('Separate with coma') }}</small>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Meta Image') }}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="image"
                                        data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="meta_img" value="{{ $product->meta_img }}"
                                            class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{ translate('Slug') }}</label>
                                    <input type="text" placeholder="{{ translate('Slug') }}" id="slug"
                                        name="slug" value="{{ $product->slug }}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{ translate('Price') }}
                            </h5>
                            <div class="w-100">
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Unit price') }} <span class="text-danger">*</span></label>
                                    <input type="number" lang="en" min="0" step="0.01"
                                        placeholder="{{ translate('Unit price') }}" name="unit_price"
                                        class="form-control" value="{{ $product->unit_price }}" required>
                                </div>
                                @if (addon_is_activated('gst_system'))
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('HSN Code')}}</label>
                                        <input type="text" lang="en" value="{{ $product->hsn_code }}"  placeholder="{{ translate('HSN Code') }}" name="hsn_code" class="form-control">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('GST Rate (%)')}}</label>
                                        <input type="number" lang="en" min="0" value="{{ $product->gst_rate }}" step="0.01" placeholder="{{ translate('GST Rate') }}" name="gst_rate" class="form-control">
                                    </div>
                                @else
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
                                            <label class="col-from-label fs-13">{{$tax->name}}</label>
                                            <div class="row gutters-5">
                                                <div class="col-lg-8">
                                                    <input type="hidden" value="{{$tax->id}}" name="tax_id[]">
                                                    <input type="number" lang="en" min="0" step="0.01"
                                                        placeholder="{{ translate('tax') }}" name="tax[]" class="form-control"
                                                        value="{{ $tax_amount }}" required>
                                                </div>
                                                <div class="col-lg-4">
                                                    <select class="form-control aiz-selectpicker" name="tax_type[]" required>
                                                        <option value="amount" @if($tax_type == 'amount') selected @endif>{{translate('Flat')}}</option>
                                                        <option value="percent" @if($tax_type == 'percent') selected @endif>{{translate('Percent')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Discount Date Range') }}</label>
                                    <input type="text" class="form-control aiz-date-range"
                                        value="{{ $start_date && $end_date ? $start_date . ' to ' . $end_date : '' }}" name="date_range"
                                        placeholder="{{ translate('Select Date') }}" data-time-picker="true"
                                        data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                                </div>

                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{ translate('Discount') }}</label>
                                    <div class="row gutters-5">
                                        <div class="col-lg-8">
                                            <input type="number" lang="en" min="0" step="0.01"
                                                placeholder="{{ translate('Discount') }}" name="discount" class="form-control"
                                                value="{{ $product->discount }}" required>
                                        </div>
                                        <div class="col-lg-4">
                                            <select class="form-control aiz-selectpicker" name="discount_type" required>
                                                <option value="amount" @if ($product->discount_type == 'amount') selected @endif>{{ translate('Flat') }}</option>
                                                <option value="percent" @if ($product->discount_type == 'percent') selected @endif>{{ translate('Percent') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{ translate('Product Information') }}
                            </h5>
                            <div class="w-100">
                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{ translate('Description') }} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                                    <textarea class="aiz-text-editor" name="description">{{ $product->getTranslation('description', $lang) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Frequently Bought Products --}}
                    <div class="card">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{ translate('Frequently Bought') }}
                            </h5>
                            <div class="w-100">
                                <div class="d-flex my-3"> 
                                    <div class="radio mar-btm mr-5 ml-4 d-flex align-items-center">
                                        <input
                                            id="fq_bought_select_products"
                                            type="radio"
                                            name="frequently_bought_selection_type" 
                                            value="product" 
                                            onchange="fq_bought_product_selection_type()"
                                            @if($product->frequently_bought_selection_type == 'product') checked @endif
                                        >
                                        <label for="fq_bought_select_products" class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Product')}}</label>
                                    </div>
                                    <div class="radio mar-btm mr-3 d-flex align-items-center">
                                        <input
                                            id="fq_bought_select_category"
                                            type="radio"
                                            name="frequently_bought_selection_type"
                                            value="category"
                                            onchange="fq_bought_product_selection_type()"
                                            @if($product->frequently_bought_selection_type == 'category') checked @endif
                                        >
                                        <label for="fq_bought_select_category" class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Category')}}</label>
                                    </div>
                                </div>
                                
                                <div class="px-0">
                                    <div class="fq_bought_select_product_div @if($product->frequently_bought_selection_type != 'product') d-none @endif">
                                        @php 
                                            $fq_bought_products = $product->frequently_bought_products()->where('category_id', null)->get();
                                        @endphp
        
                                        <div id="selected-fq-bought-products">
                                            @if(count($fq_bought_products) > 0)
                                                @foreach($fq_bought_products as $fQBproduct)
                                                    <div class="form-group row gutters-5 remove-parent">
                                                        <div class="col-lg-3">
                                                            <input type="text" class="form-control" value="{{ $fQBproduct->frequently_bought_product->getTranslation('name') }}" disabled>
                                                        </div>
                                                        <div class="col-lg-9">
                                                            <div class="row gutters-5">
                                                                <div class="col-lg-10">
                                                                    <input type="hidden" name="fq_bought_product_ids[]" value="{{ $fQBproduct->frequently_bought_product->id }}">
                                                                    <input type="text" class="form-control" value="{{ $fQBproduct->frequently_bought_product->thumbnail_img}}" disabled>
                                                                </div>
                                                                <div class="col-lg-2">
                                                                    <button type="button" class="btn btn-icon btn-sm btn-danger custom-rounded" data-toggle="remove-parent" data-parent=".remove-parent">
                                                                        <i class="las la-times"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
        
                                        <button 
                                            type="button" 
                                            class="btn btn-block border border-dashed hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                            onclick="showFqBoughtProductModal()">
                                            <i class="las la-plus"></i>
                                            <span class="ml-2">{{ translate('Add More') }}</span>
                                        </button>
                                    </div>
        
                                    {{-- Select Category for Frequently Bought Product --}}
                                    <div class="fq_bought_select_category_div @if($product->frequently_bought_selection_type != 'category') d-none @endif">
                                        @php 
                                            $fq_bought_product_category_id = $product->frequently_bought_products()->where('category_id','!=', null)->first(); 
                                            $fqCategory = $fq_bought_product_category_id != null ? $fq_bought_product_category_id->category_id : null;
                                        @endphp
                                        <div class="form-group mb-0">
                                            <label class="col-from-label fs-13">{{translate('Category')}}</label>
                                            <select
                                                class="form-control aiz-selectpicker"
                                                data-placeholder="{{ translate('Select a Category')}}"
                                                name="fq_bought_product_category_id"
                                                data-live-search="true"
                                                data-selected="{{ $fqCategory }}"
                                            >
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

                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{ translate('Product Category') }}
                            </h5>
                            <div class="w-100">
                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{translate('Product Category')}} <span
                                            class="text-danger">*</span></label>
                                    <input type="hidden" name="category_ids[]" id="category_ids_hidden"
                                        value="{{ $product->category_id }}">
                                    <select
                                        class="form-control aiz-selectpicker @error('category_id') is-invalid @enderror"
                                        name="category_id" id="category_id" data-live-search="true" required
                                        onchange="$('#category_ids_hidden').val(this.value);">
                                        <option value="">{{ translate('Select Category') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                @selected($product->category_id == $category->id)>
                                                {{ $category->getTranslation('name') }}
                                            </option>
                                            @foreach ($category->childrenCategories as $childCategory)
                                                <option value="{{ $childCategory->id }}"
                                                    @selected($product->category_id == $childCategory->id)>
                                                    &nbsp;&nbsp;&nbsp;-- {{ $childCategory->getTranslation('name') }}
                                                </option>
                                                @foreach ($childCategory->childrenCategories as $subChildCategory)
                                                    <option value="{{ $subChildCategory->id }}"
                                                        @selected($product->category_id == $subChildCategory->id)>
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----
                                                        {{ $subChildCategory->getTranslation('name') }}
                                                    </option>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group mb-0 text-right mb-2">
                        <button type="submit" name="button" value="publish"
                            class="btn btn-primary btn-md fs-13 fw-700 px-5 radius-0">{{translate('Update Product')}}</button>
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

    $(document).ready(function() {
        AIZ.plugins.tagify();
        fq_bought_product_selection_type();
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
        $.post('{{ route('product.search') }}', { _token: AIZ.data.csrf, product_id: productID, search_key:searchKey, category:fqBroughCategory, product_type:"digital" }, function(data){
            $('#product-list').html(data);
            AIZ.plugins.fooTable();
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

        $.post('{{ route('get-selected-products') }}', { _token: AIZ.data.csrf, product_ids:productIds}, function(data){
            $('#fq-bought-product-select-modal').modal('hide');
            $('#selected-fq-bought-products').html(data);
            AIZ.plugins.fooTable();
        });
    }

</script>
@endsection
