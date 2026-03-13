@extends('seller.layouts.app')

@section('panel_content')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <div class="page-content">
        <div class="aiz-titlebar text-left mt-2 pb-2 px-3 px-md-2rem border-bottom border-gray">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h3">{{ translate('Edit Product') }}</h1>
                </div>
            </div>
        </div>

        <div>
            <!-- Main Form Content -->
            <div class="p-sm-3 p-lg-2rem mb-2rem mb-md-0">
                <!-- Error Meassages -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{route('seller.products.update', $product->id)}}" method="POST" enctype="multipart/form-data" id="aizSubmitForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input type="hidden" name="tab" id="tab">

                    <ul class="nav nav-tabs nav-fill language-bar">
                        @foreach (get_all_active_language() as $key => $language)
                            <li class="nav-item">
                                <a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3"
                                    href="{{ route('seller.products.edit', ['id' => $product->id, 'lang' => $language->code]) }}">
                                    <img src="{{ static_asset('assets/img/flags/' . $language->code . '.png') }}" height="11"
                                        class="mr-1">
                                    <span>{{$language->name}}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="product-form-sections">
                        <!-- General -->
                        <div class="card mb-4" id="general">
                            <div class="bg-white p-3 p-sm-2rem">
                                <!-- Product Information -->
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('Product Information')}}
                                </h5>
                                <div class="w-100">
                                    <div class="row">
                                        <div class="col-xxl-7 col-xl-6">
                                            <!-- Product Name -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{translate('Product Name')}} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                    name="name" id="product_name" placeholder="{{translate('Product Name')}}"
                                                    value="{{ $product->getTranslation('name', $lang) }}">
                                            </div>
                                            <!-- Brand -->
                                            <div class="form-group mb-2" id="brand">
                                                <label class="col-from-label fs-13">{{translate('Brand')}}</label>
                                                <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id"
                                                    data-live-search="true">
                                                    <option value="">{{ translate('Select Brand') }}</option>
                                                    @foreach (\App\Models\Brand::all() as $brand)
                                                        <option value="{{ $brand->id }}" @selected($product->brand_id == $brand->id)>
                                                            {{ $brand->getTranslation('name') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">{{translate("You can choose a brand if you'd like to display your product by brand.")}}</small>
                                            </div>
                                            <!-- Unit -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{translate('Unit')}} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" letter-only
                                                    class="form-control @error('unit') is-invalid @enderror" name="unit"
                                                    placeholder="{{ translate('Unit (e.g. KG, Pc etc)') }}"
                                                    value="{{$product->getTranslation('unit', $lang)}}">
                                            </div>
                                            <!-- Weight -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{translate('Weight')}}
                                                    <small>({{ translate('In Kg') }})</small></label>
                                                <input type="number" class="form-control" name="weight"
                                                    value="{{ $product->weight}}" step="0.01" placeholder="0.00">
                                            </div>
                                            <!-- Quantity -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{translate('Minimum Purchase Qty')}}
                                                    <span class="text-danger">*</span></label>
                                                <input type="number" lang="en"
                                                    class="form-control @error('min_qty') is-invalid @enderror"
                                                    name="min_qty"
                                                    value="@if($product->min_qty <= 1){{1}}@else{{$product->min_qty}}@endif"
                                                    min="1" step="1" integer-only required>
                                                <small class="text-muted">{{translate("The minimum quantity needs to be purchased by your customer.")}}</small>
                                            </div>
                                            <!-- Tags -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{translate('Tags')}}</label>
                                                <input type="text" class="form-control aiz-tag-input" name="tags[]"
                                                    id="tags" value="{{ $product->tags }}"
                                                    placeholder="{{ translate('Type to add a tag') }}"
                                                    data-role="tagsinput">
                                                <small class="text-muted">{{translate('This is used for search. Input those words by which cutomer can find this product.')}}</small>
                                            </div>

                                            @if (addon_is_activated('pos_system'))
                                                <!-- Barcode -->
                                                <div class="form-group mb-2">
                                                    <label class="col-from-label fs-13">{{translate('Barcode')}}</label>
                                                    <input type="text" class="form-control" name="barcode"
                                                        placeholder="{{ translate('Barcode') }}"
                                                        value="{{ $product->barcode }}">
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Product Category -->
                                        <div class="col-xxl-5 col-xl-6">
                                            <div class="form-group mb-2">
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
                                                @error('category_ids')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <!-- HS Code - Server-side rendered -->
                                            <div class="form-group mb-2 mt-3">
                                                <label
                                                    class="col-from-label fs-13">{{translate('TN VED (HS Code)')}}</label>
                                                <select class="form-control" name="hsn_code" id="hsn_code_select">
                                                    @if($product->hsn_code)
                                                        <option value="{{ $product->hsn_code }}" selected>
                                                            {{ $product->hsn_code }}
                                                        </option>
                                                    @endif
                                                </select>
                                                <small class="text-muted">{{ translate('Used for international shipping and customs.') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="fs-13 mb-0">{{translate('Description')}}</label>
                                            @if(Route::has('seller.ai.generate'))
                                            <button type="button" class="btn btn-sm btn-soft-primary"
                                                onclick="generateDescriptionAI()">
                                                <i class="las la-magic"></i> {{translate('Generate description by AI')}}
                                            </button>
                                            @endif
                                        </div>
                                        <div class="">
                                            <textarea class="aiz-text-editor" id="product_description"
                                                name="description">{{ $product->getTranslation('description', $lang) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vat & TAX -->
                                <h5 class="mb-3 mt-4 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('Vat & TAX')}}
                                </h5>
                                <div class="w-100">
                                    @foreach(\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                                        <label for="name">
                                            {{$tax->name}}
                                            <input type="hidden" value="{{$tax->id}}" name="tax_id[]">
                                        </label>

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

                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <input type="number" lang="en" min="0" value="{{ $tax_amount }}" step="0.01"
                                                    placeholder="{{ translate('Tax') }}" name="tax[]" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <select class="form-control aiz-selectpicker" name="tax_type[]">
                                                    <option value="amount" @selected($tax_type == 'amount')>
                                                        {{translate('Flat')}}
                                                    </option>
                                                    <option value="percent" @selected($tax_type == 'percent')>
                                                        {{translate('Percent')}}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Files & Media -->
                        <div class="card mb-4" id="files_and_media">
                            <div class="bg-white p-3 p-sm-2rem">
                                <!-- Product Files & Media -->
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{ translate('Product Files & Media') }}
                                </h5>
                                <div class="w-100">
                                    <!-- Gallery Images -->
                                    <div class="form-group mb-2">
                                        <label class="col-form-label"
                                            for="signinSrEmail">{{ translate('Gallery Images') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image"
                                            data-multiple="true">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse') }}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="photos" value="{{ $product->photos }}"
                                                class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                        <small
                                            class="text-muted">{{ translate('These images are visible in product details page gallery. Minimum dimensions required: 900px width X 900px height.') }}</small>
                                    </div>
                                    <!-- Thumbnail Image -->
                                    <div class="form-group mb-2">
                                        <label class="col-form-label"
                                            for="signinSrEmail">{{ translate('Thumbnail Image') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse') }}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="thumbnail_img" value="{{ $product->thumbnail_img }}"
                                                class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                        <small
                                            class="text-muted">{{ translate('This image is visible in all product box. Minimum dimensions required: 195px width X 195px height. Keep some blank space around main object of your image as we had to crop some edge in different devices to make it responsive.') }}</small>
                                    </div>

                                    <!-- Short Video  -->
                                    <div class="form-group mb-2">
                                        <label class="col-form-label" for="signinSrEmail">{{ translate('Videos') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="video"
                                            data-multiple="true">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse') }}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="short_video" value="{{ $product->short_video }}"
                                                class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                        <small
                                            class="text-muted">{{ translate('Try to upload videos under 30 seconds for better performance.') }}</small>
                                    </div>

                                    <!-- short_video_thumbnail Video  -->
                                    <div class="form-group mb-2">
                                        <label class="col-form-label"
                                            for="signinSrEmail">{{ translate("Video Thumbnails") }}</label>

                                        <div class="input-group" data-toggle="aizuploader" data-type="image"
                                            data-multiple="true">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse') }}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="short_video_thumbnail"
                                                value="{{ $product->short_video_thumbnail }}" class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                        <small class="text-muted">
                                            {{ translate('Add thumbnails in the same order as your videos. If you upload only one image, it will be used for all videos.') }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Video Link -->
                                <div class="form-group mb-2">
                                    <label
                                        class="col-form-label pt-1">{{ translate('Youtube video / shorts link') }}</label>
                                    <div class="video-provider-link">
                                        @if (empty($product->video_link))
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control" name="video_link[]" value=""
                                                        placeholder="{{ translate('Video Link') }}">
                                                </div>
                                            </div>
                                        @endif

                                        @foreach ($product->video_link ?? [] as $index => $video_link)
                                            <div class="row mb-2">
                                                <div class="col-md-{{ $index == 0 ? '12' : '11' }}">
                                                    <input type="text" class="form-control" name="video_link[]"
                                                        value="{{ $video_link }}" placeholder="{{ translate('Video Link') }}">
                                                </div>
                                                @if($index > 0)
                                                    <div class="col-1 d-flex justify-content-end">
                                                        <button type="button" class="mt-1 btn btn-icon btn-sm btn-soft-danger"
                                                            data-toggle="remove-parent" data-parent=".row">
                                                            <i class="las la-times"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="form-group row d-flex justify-content-end " style="width: 100%">
                                        <button type="button"
                                            class="btn btn-block border border-dashed hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center ml-3 mt-3"
                                            data-toggle="add-more" data-content='<div class="row mb-2">
                                                <div class="col">
                                                    <input type="text" class="form-control" name="video_link[]" value="" placeholder="{{ translate('Youtube video or short link') }}">
                                                </div>
                                                <div class="col-auto d-flex justify-content-end">
                                                    <button type="button" class="my-1 pt-2 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>'
                                            data-target=".video-provider-link">
                                            <i class="las la-plus mr-2"></i>
                                            {{ translate('Add Another') }}
                                        </button>
                                    </div>
                                </div>

                                <!-- PDF Specification -->
                                <div class="form-group mb-2">
                                    <label class="col-form-label"
                                        for="signinSrEmail">{{ translate('PDF Specification') }}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="document">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse') }}
                                            </div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="pdf" value="{{ $product->pdf }}" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price & Stock -->
                        <div class="card mb-4" id="price_and_stocks">
                            <div class="bg-white p-3 p-sm-2rem">
                                <!-- tab Title -->
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('Product price & stock')}}
                                </h5>
                                <div class="w-100">
                                    <!-- Colors -->
                                    <div class="form-group row gutters-5">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" value="{{translate('Colors')}}"
                                                disabled>
                                        </div>
                                        <div class="col-md-8">
                                            @php
                                                $product_colors = json_decode($product->colors ?? '[]');
                                            @endphp
                                            <select class="form-control aiz-selectpicker" data-live-search="true"
                                                data-selected-text-format="count" name="colors[]" id="colors" multiple @disabled(count($product_colors) < 1)>
                                                @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                                    <option value="{{ $color->code }}"
                                                        data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>"
                                                        @selected(in_array($color->code, $product_colors))></option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input value="1" type="checkbox" name="colors_active" @checked(count($product_colors) > 0)>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Attributes -->
                                    <div class="form-group row gutters-5">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" value="{{translate('Attributes')}}"
                                                disabled>
                                        </div>
                                        <div class="col-md-8">
                                            @php
                                                $product_attributes = json_decode($product->attributes ?? '[]', true);
                                            @endphp
                                            <select name="choice_attributes[]" id="choice_attributes"
                                                data-selected-text-format="count" data-live-search="true"
                                                class="form-control aiz-selectpicker" multiple
                                                data-placeholder="{{ translate('Choose Attributes') }}">
                                                @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                                    <option value="{{ $attribute->id }}" @selected($product_attributes != null && in_array($attribute->id, $product_attributes))>
                                                        {{ $attribute->getTranslation('name') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <p>{{ translate('Choose the attributes of this product and then input values of each attribute') }}</p>
                                    </div>

                                    <!-- choice options -->
                                    <div class="customer_choice_options" id="customer_choice_options">
                                        @foreach (json_decode($product->choice_options ?? '[]') as $key => $choice_option)
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <input type="hidden" name="choice_no[]"
                                                        value="{{ $choice_option->attribute_id }}">
                                                    <input type="text" class="form-control" name="choice[]"
                                                        value="{{ optional(\App\Models\Attribute::find($choice_option->attribute_id))->getTranslation('name') }}"
                                                        placeholder="{{ translate('Choice Title') }}" readonly title="{{ optional(\App\Models\Attribute::find($choice_option->attribute_id))->getTranslation('name') }}">
                                                </div>
                                                <div class="col-md-8">
                                                    <select class="form-control aiz-selectpicker attribute_choice"
                                                        data-live-search="true"
                                                        name="choice_options_{{ $choice_option->attribute_id }}[]"
                                                        data-selected-text-format="count" multiple required>
                                                        @foreach (\App\Models\AttributeValue::where('attribute_id', $choice_option->attribute_id)->get() as $row)
                                                            <option value="{{ $row->value }}" @selected(in_array($row->value, $choice_option->values))>
                                                                {{ $row->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Unit price -->
                                    <div class="form-group mb-2">
                                        <label class="col-from-label">{{translate('Unit price')}} <span
                                                class="text-danger">*</span></label>
                                        <input type="number" min="0" step="0.01" placeholder="{{translate('Unit price')}}"
                                            name="unit_price" class="form-control @error('unit_price') is-invalid @enderror"
                                            value="{{$product->unit_price}}">
                                    </div>

                                    @php
                                        $date_range = '';
                                        if($product->discount_start_date){
                                            $start_date = date('d-m-Y H:i:s', $product->discount_start_date);
                                            $end_date = date('d-m-Y H:i:s', $product->discount_end_date);
                                            $date_range = $start_date . ' to ' . $end_date;
                                        }
                                    @endphp
                                    <!-- Discount Date Range -->
                                    <div class="form-group mb-2">
                                        <label class="control-label"
                                            for="start_date">{{translate('Discount Date Range')}}</label>
                                        <input type="text" class="form-control aiz-date-range"
                                            value="{{ $date_range }}" name="date_range"
                                            placeholder="{{translate('Select Date')}}" data-time-picker="true"
                                            data-past-disable="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to "
                                            autocomplete="off">
                                    </div>
                                    <!-- Discount -->
                                    <div class="form-group mb-2">
                                        <label class="col-from-label">{{translate('Discount')}} <span
                                                class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="number" lang="en" step="0.01"
                                                    placeholder="{{translate('Discount')}}" name="discount"
                                                    class="form-control @error('discount') is-invalid @enderror"
                                                    value="{{ $product->discount }}">
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-control aiz-selectpicker" name="discount_type">
                                                    <option value="amount" @selected($product->discount_type == 'amount')>{{translate('Flat')}}</option>
                                                    <option value="percent" @selected($product->discount_type == 'percent')>{{translate('Percent')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    @if(addon_is_activated('club_point'))
                                        <!-- club point -->
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">{{translate('Set Point')}}</label>
                                            <input type="number" lang="en" min="0" value="{{ $product->earn_point }}"
                                                step="0.01" placeholder="{{ translate('1') }}" name="earn_point"
                                                class="form-control">
                                        </div>
                                    @endif

                                    <!-- SKU -->
                                    <div class="form-group mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="col-from-label mb-0">{{translate('SKU')}}</label>
                                            <button type="button" class="btn btn-sm btn-soft-primary"
                                                onclick="generateSKU()">
                                                <i class="las la-random"></i> {{ translate('Auto Generate') }}
                                            </button>
                                        </div>
                                        <input type="text" placeholder="{{ translate('SKU') }}"
                                            value="{{ optional($product->stocks->first())->sku }}" name="sku" id="sku_input"
                                            class="form-control">
                                    </div>

                                    <div id="show-hide-div">
                                        <!-- Quantity -->
                                        <div class="form-group" id="quantity">
                                            <label class="col-from-label">{{translate('Quantity')}} <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" lang="en"
                                                value="{{ optional($product->stocks->first())->qty ?? 0 }}" step="1"
                                                integer-only placeholder="{{translate('Quantity')}}" name="current_stock"
                                                class="form-control">
                                        </div>
                                    </div>

                                    @if(get_setting('product_external_link_for_seller') == 1)
                                        <!-- External link -->
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">{{translate('External link')}}</label>
                                            <input type="text" placeholder="{{ translate('External link') }}"
                                                name="external_link" value="{{ $product->external_link }}" class="form-control">
                                            <small class="text-muted">{{translate('Leave it blank if you do not use external site link')}}</small>
                                        </div>
                                        <!-- External link button text -->
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">{{translate('External link button text')}}</label>
                                            <input type="text" placeholder="{{ translate('External link button text') }}"
                                                name="external_link_btn" value="{{ $product->external_link_btn }}"
                                                class="form-control">
                                            <small class="text-muted">{{translate('Leave it blank if you do not use external site link')}}</small>
                                        </div>
                                    @endif
                                    <br>
                                    <!-- sku combination -->
                                    <div class="sku_combination" id="sku_combination"></div>
                                </div>

                                <!-- Low Stock Quantity -->
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('Low Stock Quantity Warning')}}
                                </h5>
                                <div class="w-100 mb-3">
                                    <div class="form-group mb-2">
                                        <label class="col-from-label">{{translate('Quantity')}}</label>
                                        <input type="number" name="low_stock_quantity"
                                            value="{{ $product->low_stock_quantity }}" min="0" step="1" integer-only
                                            class="form-control">
                                    </div>
                                </div>

                                <!-- Stock Visibility State -->
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('Stock Visibility State')}}
                                </h5>
                                <div class="w-100">
                                    <!-- Show Stock Quantity -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">{{translate('Show Stock Quantity')}}</label>
                                        <div class="col-md-9">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="radio" name="stock_visibility_state" value="quantity"
                                                    @checked($product->stock_visibility_state == 'quantity')>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Show Stock With Text Only -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">{{translate('Show Stock With Text Only')}}</label>
                                        <div class="col-md-9">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="radio" name="stock_visibility_state" value="text"
                                                    @checked($product->stock_visibility_state == 'text')>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Hide Stock -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">{{translate('Hide Stock')}}</label>
                                        <div class="col-md-9">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="radio" name="stock_visibility_state" value="hide"
                                                    @checked($product->stock_visibility_state == 'hide')>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Settings Toggle -->
                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-soft-primary px-5 py-2 fw-700 shadow-sm"
                                data-toggle="collapse" href="#advancedSettings" aria-expanded="false"
                                aria-controls="advancedSettings">
                                <i class="las la-cog mr-2"></i>{{ translate('Advanced Settings') }}
                            </button>
                        </div>

                        <div class="collapse" id="advancedSettings">
                            <!-- SEO -->
                            <div class="card mb-4" id="seo">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{translate('SEO Meta Tags')}}
                                    </h5>
                                    <div class="w-100">
                                        <!-- Meta Title -->
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">{{translate('Meta Title')}}</label>
                                            <input type="text" class="form-control" name="meta_title"
                                                value="{{ $product->meta_title }}"
                                                placeholder="{{translate('Meta Title')}}">
                                        </div>
                                        <!-- Description -->
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">{{translate('Description')}}</label>
                                            <textarea name="meta_description" rows="8"
                                                class="form-control">{{ $product->meta_description }}</textarea>
                                        </div>
                                        <!-- meta keywords -->
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">{{translate('Keywords')}}</label>
                                            <textarea class="resize-off form-control" name="meta_keywords"
                                                placeholder="{{ translate('Keyword, Keyword') }}">{{ $product->meta_keywords }}</textarea>
                                            <small class="text-muted">{{ translate('Separate with coma') }}</small>
                                        </div>
                                        <!-- Meta Image -->
                                        <div class="form-group mb-2">
                                            <label class="col-form-label">{{ translate('Meta Image') }}</label>
                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                        {{ translate('Browse')}}
                                                    </div>
                                                </div>
                                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                                <input type="hidden" name="meta_img" value="{{ $product->meta_img }}"
                                                    class="selected-files">
                                            </div>
                                            <div class="file-preview box sm"></div>
                                        </div>
                                        <!-- Slug -->
                                        <div class="form-group mb-2">
                                            <label class="col-form-label">{{translate('Slug')}}</label>
                                            <input type="text" placeholder="{{translate('Slug')}}" id="slug" name="slug"
                                                value="{{ $product->slug }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping -->
                            <div class="card mb-4" id="shipping">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{translate('Shipping Configuration')}}
                                    </h5>
                                    <div class="w-100">
                                        <!-- Cash On Delivery -->
                                        @if (get_setting('cash_payment') == '1')
                                            <div class="form-group row">
                                                <label class="col-md-3 col-from-label">{{translate('Cash On Delivery')}}</label>
                                                <div class="col-md-9">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="checkbox" name="cash_on_delivery" value="1"
                                                            @checked($product->cash_on_delivery == 1)>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        @else
                                            <p>{{ translate('Cash On Delivery activation is maintained by Admin.') }}</p>
                                        @endif

                                        @if (get_setting('shipping_type') == 'product_wise_shipping')
                                            <!-- Free Shipping -->
                                            <div class="form-group row">
                                                <label class="col-md-3 col-from-label">{{translate('Free Shipping')}}</label>
                                                <div class="col-md-9">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="radio" name="shipping_type" value="free"
                                                            @checked($product->shipping_type == 'free')>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <!-- Flat Rate -->
                                            <div class="form-group row">
                                                <label class="col-md-3 col-from-label">{{translate('Flat Rate')}}</label>
                                                <div class="col-md-9">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="radio" name="shipping_type" value="flat_rate"
                                                            @checked($product->shipping_type == 'flat_rate')>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <!-- Shipping cost -->
                                            <div class="flat_rate_shipping_div" style="display: none">
                                                <div class="form-group mb-2">
                                                    <label class="col-from-label">{{translate('Shipping cost')}}</label>
                                                    <input type="number" lang="en" min="0" value="{{ $product->shipping_cost }}"
                                                        step="0.01" placeholder="{{ translate('Shipping cost') }}"
                                                        name="flat_shipping_cost" class="form-control">
                                                </div>
                                            </div>
                                            <!-- Is Product Quantity Mulitiply -->
                                            <div class="form-group row">
                                                <label class="col-md-3 col-from-label">{{translate('Is Product Quantity Mulitiply')}}</label>
                                                <div class="col-md-9">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="checkbox" name="is_quantity_multiplied" value="1"
                                                            @checked($product->is_quantity_multiplied == 1)>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        @else
                                            <p>{{ translate('Shipping configuration is maintained by Admin.') }}</p>
                                        @endif
                                    </div>

                                    <!-- Estimate Shipping Time -->
                                    <h5 class="mb-3 mt-4 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{translate('Estimate Shipping Time')}}
                                    </h5>
                                    <div class="w-100">
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">{{translate('Shipping Days')}}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="est_shipping_days"
                                                    value="{{ $product->est_shipping_days }}" min="1" step="1" integer-only
                                                    placeholder="{{translate('Shipping Days')}}">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">{{translate('Days')}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Warranty -->
                            <div class="card mb-4" id="warranty">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{translate('Warranty')}}
                                    </h5>
                                    <div class="form-group row">
                                        <label class="col-md-2 col-from-label">{{translate('Warranty')}}</label>
                                        <div class="col-md-10">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="has_warranty" onchange="warrantySelection()"
                                                    @checked($product->has_warranty == 1)>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="w-100 warranty_selection_div @if($product->has_warranty != 1) d-none @endif">
                                        <div class="form-group row">
                                            <div class="col-md-2"></div>
                                            <div class="col-md-10">
                                                <select class="form-control aiz-selectpicker" name="warranty_id"
                                                    id="warranty_id" data-selected="{{ $product->warranty_id }}"
                                                    data-live-search="true" @if($product->has_warranty == 1) required @endif>
                                                    <option value="">{{ translate('Select Warranty') }}</option>
                                                    @foreach (\App\Models\Warranty::all() as $warranty)
                                                        <option value="{{ $warranty->id }}" @selected($product->warranty_id == $warranty->id)>
                                                            {{ $warranty->getTranslation('text') }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <input type="hidden" name="warranty_note_id" id="warranty_note_id"
                                                    value="{{$product->warranty_note_id}}">

                                                <h5 class="fs-14 fw-600 mb-3 mt-4 pb-3"
                                                    style="border-bottom: 1px dashed #e4e5eb;">
                                                    {{translate('Warranty Note')}}
                                                </h5>
                                                <div id="warranty_note">
                                                    @if($product->warrantyNote != null)
                                                        <div class="border border-gray my-2 p-2">
                                                            {{ $product->warrantyNote->getTranslation('description') ?? '' }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <button type="button"
                                                    class="btn btn-block border border-dashed hov-bg-soft-secondary mt-2 fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                                    onclick="noteModal('warranty')">
                                                    <i class="las la-plus"></i>
                                                    <span class="ml-2">{{ translate('Select Warranty Note') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End Advanced Settings Collapse -->
                    </div>

                    <!-- Update Button -->
                    <div class="mt-4 text-right">
                        <button type="submit" name="button"
                            class="mx-2 btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success action-btn">{{ translate('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <!-- Frequently Bought Product Select Modal -->
    @include('modals.product_select_modal')

    {{-- Note Modal --}}
    @include('modals.note_modal')
@endsection

@section('script')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            show_hide_shipping_div();
        });

        $("[name=shipping_type]").on("change", function () {
            show_hide_shipping_div();
        });

        function show_hide_shipping_div() {
            var shipping_val = $("[name=shipping_type]:checked").val();
            $(".flat_rate_shipping_div").hide();
            if (shipping_val == 'flat_rate') {
                $(".flat_rate_shipping_div").show();
            }
        }

        function add_more_customer_choice_option(i, name) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: '{{ route('seller.products.add-more-choice-option') }}',
                data: { attribute_id: i },
                success: function (data) {
                    var obj = JSON.parse(data);
                    $('#customer_choice_options').append('\
                        <div class="form-group row">\
                            <div class="col-md-3">\
                                <input type="hidden" name="choice_no[]" value="'+ i + '">\
                                <input type="text" class="form-control" name="choice[]" value="'+ name + '" placeholder="{{ translate('Choice Title') }}" readonly title="' + name + '">\
                            </div>\
                            <div class="col-md-8">\
                                <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_'+ i + '[]" data-selected-text-format="count" multiple required>\
                                    '+ obj + '\
                                </select>\
                            </div>\
                        </div>');
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            });
        }

        $('input[name="colors_active"]').on('change', function () {
            if (!$('input[name="colors_active"]').is(':checked')) {
                $('#colors').prop('disabled', true);
            } else {
                $('#colors').prop('disabled', false);
            }
            AIZ.plugins.bootstrapSelect('refresh');
            update_sku();
        });

        $(document).on("change", ".attribute_choice", function () {
            update_sku();
        });

        $('#colors').on('change', function () {
            update_sku();
        });

        function delete_row(em) {
            $(em).closest('.form-group').remove();
            update_sku();
        }

        function update_sku() {
            $.ajax({
                type: "POST",
                url: '{{ route('seller.products.sku_combination_edit') }}',
                data: $('#aizSubmitForm').serialize(),
                success: function (data) {
                    $('#sku_combination').html(data);
                    setTimeout(() => {
                        AIZ.uploader.previewGenerate();
                    }, "2000");
                    if (data.trim().length > 1) {
                        $('#show-hide-div').hide();
                        AIZ.plugins.sectionFooTable('#sku_combination');
                        $('input[name="current_stock"]').removeAttr('integer-only');

                        $('#sku_combination input[name="sku_combinations[]"]').each(function () {
                            checkSKUAvailability(this);
                        });
                    } else {
                        $('#show-hide-div').show();
                        $('input[name="current_stock"]').attr('integer-only', 'true');
                    }
                }
            });
        }

        AIZ.plugins.tagify();

        $(document).ready(function () {
            update_sku();
            $('.remove-files').on('click', function () {
                $(this).parents(".col-md-4").remove();
            });
        });

        $('#choice_attributes').on('change', function () {
            $.each($("#choice_attributes option:selected"), function (j, attribute) {
                flag = false;
                $('input[name="choice_no[]"]').each(function (i, choice_no) {
                    if ($(attribute).val() == $(choice_no).val()) {
                        flag = true;
                    }
                });
                if (!flag) {
                    add_more_customer_choice_option($(attribute).val(), $(attribute).text());
                }
            });

            $('input[name="choice_no[]"]').each(function () {
                var choice_no = $(this).val();
                var found = false;
                $('#choice_attributes option:selected').each(function () {
                    if ($(this).val() == choice_no) {
                        found = true;
                    }
                });
                if (!found) {
                    $(this).parent().parent().remove();
                }
            });

            update_sku();
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

        // Warranty
        function warrantySelection() {
            if ($('input[name="has_warranty"]').is(':checked')) {
                $('.warranty_selection_div').removeClass('d-none');
                $('#warranty_id').attr('required', true);
            } else {
                $('.warranty_selection_div').addClass('d-none');
                $('#warranty_id').removeAttr('required');
            }
        }

        function noteModal(noteType) {
            $.post('{{ route('get_notes') }}', { _token: '{{ csrf_token() }}', note_type: noteType }, function (data) {
                $('#note_modal #note_modal_content').html(data);
                $('#note_modal').modal('show', { backdrop: 'static' });
            });
        }

        function addNote(noteId, noteType) {
            var noteDescription = $('#note_description_' + noteId).val();
            $('#' + noteType + '_note_id').val(noteId);
            $('#' + noteType + '_note').html(noteDescription);
            $('#' + noteType + '_note').addClass('border border-gray my-2 p-2');
            $('#note_modal').modal('hide');
        }

        function generateSKU() {
            let name = $('input[name="name"]').val();
            let prefix = "PRD-";
            if (name && name.trim().length >= 3) {
                let words = name.trim().split(/\s+/);
                if (words.length >= 3) {
                    prefix = (words[0][0] + words[1][0] + words[2][0]).toUpperCase() + "-";
                } else {
                    prefix = name.trim().substring(0, 3).toUpperCase() + "-";
                }
            }
            let randomNum = Math.floor(100000 + Math.random() * 900000);
            $('#sku_input').val(prefix + randomNum);
            AIZ.plugins.notify('success', '{{ translate("Generated meaningful SKU successfully.") }}');
            if (typeof update_sku === 'function') { update_sku(); }
        }

        function checkSKUAvailability(el) {
            let sku = $(el).val();
            if (!sku || sku.length < 2) {
                $(el).removeClass('is-invalid is-valid');
                $(el).next('.invalid-feedback').remove();
                return;
            }
            let product_id = $('input[name="id"]').val() || null;
            $.get('{{ route('products.check-sku-availability') }}', { sku: sku, product_id: product_id }, function (data) {
                if (data.exists) {
                    $(el).addClass('is-invalid').removeClass('is-valid');
                    if (!$(el).next('.invalid-feedback').length) {
                        $(el).after('<div class="invalid-feedback text-danger small mt-1">{{ translate("This SKU already exists in your catalog.") }}</div>');
                    }
                } else {
                    $(el).addClass('is-valid').removeClass('is-invalid');
                    $(el).next('.invalid-feedback').remove();
                }
            });
        }

        $(document).on('change', '#sku_input', function () { checkSKUAvailability(this); });
        $(document).on('change', 'input[name="sku_combinations[]"]', function () { checkSKUAvailability(this); });

        $(document).ready(function () {
            if ($('#hsn_code_select').length) {
                $('#hsn_code_select').select2({
                    placeholder: '{{ translate("Search by code or product name...") }}',
                    allowClear: true,
                    ajax: {
                        url: '{{ route("seller.products.hs_code_search") }}',
                        dataType: 'json',
                        delay: 300,
                        data: function (params) { return { q: params.term || '' }; },
                        processResults: function (data) { return { results: data }; },
                        cache: true
                    }
                });
            }
        });
    </script>
@endsection
