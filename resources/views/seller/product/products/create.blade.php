@extends('seller.layouts.app')

@section('panel_content')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <div class="page-content">
        <div class="aiz-titlebar text-left mt-2 pb-2 px-3 px-md-2rem border-bottom border-gray">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h3">{{ translate('Add New Product') }}</h1>
                </div>
                <div class="col text-right">
                    <a class="btn btn-xs btn-soft-primary" href="javascript:void(0);" onclick="clearTempdata()">
                        {{ translate('Clear Tempdata') }}
                    </a>
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

                <!-- Data type -->
                <input type="hidden" id="data_type" value="physical">

                <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data"
                    id="choice_form">
                    @csrf
                    <input type="hidden" name="added_by" value="seller">

                    <div class="product-form-sections">
                        <!-- General -->
                        <div class="card mb-4" id="general">
                            <div class="bg-white p-3 p-sm-2rem">
                                <!-- Product Information -->
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{ translate('Product Information') }}
                                </h5>
                                <div class="w-100">
                                    <div class="row">
                                        <div class="col-xxl-7 col-xl-6">
                                            <!-- Product Name -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Product Name') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="name" id="product_name"
                                                    placeholder="{{ translate('Product Name') }}" onchange="update_sku()"
                                                    required>
                                            </div>
                                            <!-- Brand -->
                                            <div class="form-group mb-2" id="brand">
                                                <label class="col-from-label fs-13">{{ translate('Brand') }}</label>
                                                <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id"
                                                    data-live-search="true">
                                                    <option value="">{{ translate('Select Brand') }}</option>
                                                    @foreach (\App\Models\Brand::all() as $brand)
                                                        <option value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small
                                                    class="text-muted">{{translate("You can choose a brand if you'd like to display your product by brand.")}}</small>
                                            </div>
                                            <!-- Unit -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Unit') }} <span
                                                        class="text-danger">*</span></label>
                                                <select
                                                    class="form-control aiz-selectpicker @error('unit') is-invalid @enderror"
                                                    name="unit" data-live-search="true" required>
                                                    <option value="{{ translate('Piece') }}" @selected(old('unit', translate('Piece')) == translate('Piece'))>{{ translate('Piece') }}
                                                    </option>
                                                    <option value="{{ translate('Service') }}"
                                                        @selected(old('unit') == translate('Service'))>
                                                        {{ translate('Service') }}
                                                    </option>
                                                    <option value="{{ translate('KG') }}"
                                                        @selected(old('unit') == translate('KG'))>{{ translate('KG') }}
                                                    </option>
                                                    <option value="{{ translate('Ton') }}"
                                                        @selected(old('unit') == translate('Ton'))>{{ translate('Ton') }}
                                                    </option>
                                                    <option value="{{ translate('Gram') }}"
                                                        @selected(old('unit') == translate('Gram'))>{{ translate('Gram') }}
                                                    </option>
                                                    <option value="{{ translate('Liter') }}"
                                                        @selected(old('unit') == translate('Liter'))>{{ translate('Liter') }}
                                                    </option>
                                                    <option value="{{ translate('Milliliter') }}"
                                                        @selected(old('unit') == translate('Milliliter'))>
                                                        {{ translate('Milliliter') }}
                                                    </option>
                                                    <option value="{{ translate('Meter') }}"
                                                        @selected(old('unit') == translate('Meter'))>{{ translate('Meter') }}
                                                    </option>
                                                    <option value="{{ translate('Sq. Meter') }}"
                                                        @selected(old('unit') == translate('Sq. Meter'))>
                                                        {{ translate('Sq. Meter') }}
                                                    </option>
                                                    <option value="{{ translate('Cubic Meter') }}"
                                                        @selected(old('unit') == translate('Cubic Meter'))>
                                                        {{ translate('Cubic Meter') }}
                                                    </option>
                                                </select>
                                            </div>
                                            <!-- Weight -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Weight') }}
                                                    <small>({{ translate('In Kg') }})</small></label>
                                                <input type="number" class="form-control" name="weight" step="0.01" value="0.00"
                                                    placeholder="0.00">
                                            </div>
                                            <!-- Minimum Purchase Qty -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Minimum Purchase Qty') }}
                                                    <span class="text-danger">*</span></label>
                                                <input type="number" lang="en" class="form-control" name="min_qty" value="1"
                                                    min="1" required>
                                            </div>
                                            <!-- Tags -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Tags') }}</label>
                                                <input type="text" class="form-control aiz-tag-input" name="tags[]"
                                                    id="product_tags"
                                                    placeholder="{{ translate('Type and hit enter to add a tag') }}">
                                                <small
                                                    class="text-muted">{{translate('This is used for search. Input those words by which cutomer can find this product. Automatically seeds with Product Name.')}}</small>
                                            </div>
                                            @if (addon_is_activated('pos_system'))
                                                <!-- Barcode -->
                                                <div class="form-group mb-2">
                                                    <label class="col-from-label fs-13">{{ translate('Barcode') }}</label>
                                                    <input type="text" class="form-control" name="barcode"
                                                        placeholder="{{ translate('Barcode') }}">
                                                </div>
                                            @endif
                                        </div>

                                        <div class="col-xxl-5 col-xl-6">
                                            <!-- Product Category -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Product Category') }} <span
                                                        class="text-danger">*</span></label>
                                                <select
                                                    class="form-control aiz-selectpicker @error('category_id') is-invalid @enderror"
                                                    name="category_id" id="category_id" data-live-search="true" required
                                                    onchange="$('#category_ids_hidden').val(this.value);">
                                                    <option value="">{{ translate('Select Category') }}</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            @selected(old('category_id') == $category->id)>
                                                            {{ $category->getTranslation('name') }}
                                                        </option>
                                                        @foreach ($category->childrenCategories as $childCategory)
                                                            <option value="{{ $childCategory->id }}"
                                                                @selected(old('category_id') == $childCategory->id)>&nbsp;&nbsp;&nbsp;--
                                                                {{ $childCategory->getTranslation('name') }}
                                                            </option>
                                                            @foreach ($childCategory->childrenCategories as $subChildCategory)
                                                                <option value="{{ $subChildCategory->id }}"
                                                                    @selected(old('category_id') == $subChildCategory->id)>
                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----
                                                                    {{ $subChildCategory->getTranslation('name') }}
                                                                </option>
                                                            @endforeach
                                                        @endforeach
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="category_ids[]" id="category_ids_hidden"
                                                    value="{{ old('category_id') }}">
                                            </div>
                                            <!-- HS Code - Server-side rendered -->
                                            <div class="form-group mb-2 mt-3">
                                                <label
                                                    class="col-from-label fs-13">{{translate('TN VED (HS Code)')}}</label>
                                                <select class="form-control" name="hsn_code" id="hsn_code_select">
                                                    <option></option>
                                                </select>
                                                <small
                                                    class="text-muted">{{ translate('Used for international shipping and customs.') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="fs-13 mb-0">{{translate('Description')}}</label>
                                            <button type="button" class="btn btn-sm btn-soft-primary"
                                                onclick="generateDescriptionAI()">
                                                <i class="las la-magic"></i> {{translate('Generate description by AI')}}
                                            </button>
                                        </div>
                                        <div class="">
                                            <textarea class="aiz-text-editor" name="description" id="product_description"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Files & Media -->
                        <div class="card mb-4" id="files_media">
                            <div class="bg-white p-3 p-sm-2rem">
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{ translate('Files & Media') }}
                                </h5>
                                <div class="w-100">
                                    <!-- Gallery Images -->
                                    <div class="form-group mb-2">
                                        <label class="col-from-label fs-13">{{ translate('Gallery Images') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image"
                                            data-multiple="true">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse') }}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="photos" class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                        <small
                                            class="text-muted">{{translate('These images are visible in product details page gallery. Minimum dimensions required: 900px width X 900px height.')}}</small>
                                    </div>
                                    <!-- Thumbnail Image -->
                                    <div class="form-group mb-2">
                                        <label class="col-from-label fs-13">{{ translate('Thumbnail Image') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse') }}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="thumbnail_img" class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                        <small
                                            class="text-muted">{{translate("This image is visible in all product box. Minimum dimensions required: 195px width X 195px height.")}}</small>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Videos -->
                                    <div class="form-group mb-2">
                                        <label class="col-from-label fs-13">{{ translate('Videos') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="video"
                                            data-multiple="true">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse') }}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="short_video" class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                        <small
                                            class="text-muted">{{ translate('Try to upload videos under 30 seconds for better performance.') }}</small>
                                    </div>
                                    <!-- Video Thumbnails -->
                                    <div class="form-group mb-2">
                                        <label class="col-from-label fs-13">{{ translate('Video Thumbnails') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image"
                                            data-multiple="true">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse') }}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="short_video_thumbnail" class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                        <small class="text-muted">
                                            {{ translate('Add thumbnails in the same order as your videos.') }}
                                        </small>
                                    </div>
                                    <!-- Youtube Link -->
                                    <div class="form-group mb-2">
                                        <label
                                            class="col-from-label fs-13">{{ translate('Youtube video / shorts link') }}</label>
                                        <div class="video-provider-link">
                                            <div class="row gutters-5">
                                                <div class="col">
                                                    <input type="text" class="form-control" name="video_link[]" value=""
                                                        placeholder="{{ translate('Youtube video / shorts url') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right mt-2">
                                            <button type="button" class="btn btn-secondary btn-sm" data-toggle="add-more"
                                                data-content='<div class="row gutters-5 mt-2"><div class="col"><input type="text" class="form-control" name="video_link[]" value="" placeholder="{{ translate('Youtube video or short link') }}"></div><div class="col-auto"><button type="button" class="btn btn-icon btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row"><i class="las la-times"></i></button></div></div>' data-target=".video-provider-link">
                                                {{ translate('Add Another') }}
                                            </button>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- PDF Specification -->
                                    <div class="form-group mb-2">
                                        <label class="col-from-label fs-13">{{ translate('PDF Specification') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="document">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ translate('Browse') }}
                                                </div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="pdf" class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Price & Variation -->
                        <div class="card mb-4" id="price_variation">
                            <div class="bg-white p-3 p-sm-2rem">
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{ translate('Price & Variation') }}
                                </h5>
                                <div class="w-100">
                                    <!-- Colors -->
                                    <div class="form-group row gutters-5 align-items-center">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" value="{{ translate('Colors') }}" disabled>
                                        </div>
                                        <div class="col-md-8">
                                            <select class="form-control aiz-selectpicker" data-live-search="true"
                                                name="colors[]" data-selected-text-format="count" id="colors" multiple disabled>
                                                @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                                    <option value="{{ $color->code }}"
                                                        data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>">
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="aiz-switch aiz-switch-success mb-0 d-block">
                                                <input value="1" type="checkbox" name="colors_active">
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Attributes -->
                                    <div class="form-group row gutters-5">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" value="{{ translate('Attributes') }}"
                                                disabled>
                                        </div>
                                        <div class="col-md-9">
                                            <select name="choice_attributes[]" id="choice_attributes"
                                                class="form-control aiz-selectpicker" data-live-search="true"
                                                data-selected-text-format="count" multiple
                                                data-placeholder="{{ translate('Choose Attributes') }}">
                                                @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                                    <option value="{{ $attribute->id }}">{{ $attribute->getTranslation('name') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <small
                                            class="text-muted">{{ translate('Choose the attributes of this product and then input values of each attribute') }}</small>
                                    </div>
                                    <div class="customer_choice_options" id="customer_choice_options">
                                    </div>

                                    <hr class="my-4">

                                    <div class="row gutters-5">
                                        <div class="col-md-6">
                                            <!-- Unit price -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Unit price') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" lang="en" min="0" value="{{ old('unit_price') }}" step="0.01"
                                                    placeholder="{{ translate('Unit price') }}" name="unit_price"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Discount Date Range -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Discount Date Range') }}
                                                </label>
                                                <input type="text" class="form-control aiz-date-range" name="date_range"
                                                    placeholder="{{ translate('Select Date') }}" data-time-picker="true"
                                                    data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Discount -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Discount') }} <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" lang="en" min="0" value="0" step="0.01"
                                                        placeholder="{{ translate('Discount') }}" name="discount"
                                                        class="form-control" required>
                                                    <div class="input-group-append">
                                                        <select class="form-control aiz-selectpicker" name="discount_type">
                                                            <option value="amount">{{ translate('Flat') }}</option>
                                                            <option value="percent">{{ translate('Percent') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- SKU -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('SKU') }}</label>
                                                <div class="input-group">
                                                    <input type="text" placeholder="{{ translate('SKU') }}" name="sku"
                                                        id="sku_input" class="form-control">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-soft-primary"
                                                            onclick="generateSKU()">
                                                            {{ translate('Auto Generate') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="show-hide-div">
                                            <!-- Quantity -->
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{ translate('Quantity') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" lang="en" min="0" value="0" step="1"
                                                    placeholder="{{ translate('Quantity') }}" name="current_stock"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                    </div>

                                    @if(get_setting('product_external_link_for_seller') == 1)
                                        <div class="row gutters-5">
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="col-from-label fs-13">{{ translate('External link') }}</label>
                                                    <input type="text" placeholder="{{ translate('External link') }}"
                                                        name="external_link" class="form-control">
                                                    <small
                                                        class="text-muted">{{ translate('Leave it blank if you do not use external site link') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label
                                                        class="col-from-label fs-13">{{ translate('External link button text') }}</label>
                                                    <input type="text" placeholder="{{ translate('External link button text') }}"
                                                        name="external_link_btn" class="form-control">
                                                    <small
                                                        class="text-muted">{{ translate('Leave it blank if you do not use external site link') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="sku_combination" id="sku_combination">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vat & Tax -->
                        <div class="card mb-4" id="vat_tax">
                            <div class="bg-white p-3 p-sm-2rem">
                                @if (addon_is_activated('gst_system'))
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{ translate('TN VED & GST') }}
                                    </h5>
                                    <div class="w-100">
                                        <div class="form-group mb-0">
                                            <label class="col-from-label fs-13">{{ translate('GST Rate') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" lang="en" min="0" value="0" step="0.01"
                                                placeholder="{{ translate('GST Rate') }}" name="gst_rate" class="form-control"
                                                required>
                                        </div>
                                    </div>
                                @else
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{ translate('VAT & Tax') }}
                                    </h5>
                                    <div class="w-100">
                                        @foreach (\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">
                                                    {{ $tax->name }}
                                                    <input type="hidden" value="{{ $tax->id }}" name="tax_id[]">
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" lang="en" min="0" value="0" step="0.01"
                                                        placeholder="{{ translate('Tax') }}" name="tax[]" class="form-control"
                                                        required>
                                                    <div class="input-group-append">
                                                        <select class="form-control aiz-selectpicker" name="tax_type[]">
                                                            <option value="amount">{{ translate('Flat') }}</option>
                                                            <option value="percent">{{ translate('Percent') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
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
                            <!-- SEO Meta Tags -->
                            <div class="card mb-4" id="seo">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{ translate('SEO Meta Tags') }}
                                    </h5>
                                    <div class="w-100">
                                        <div class="form-group mb-2">
                                            <label class="col-from-label fs-13">{{ translate('Meta Title') }}</label>
                                            <input type="text" class="form-control" name="meta_title"
                                                placeholder="{{ translate('Meta Title') }}">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="col-from-label fs-13">{{ translate('Description') }}</label>
                                            <textarea name="meta_description" rows="5" class="form-control"></textarea>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="col-from-label fs-13">{{ translate('Keywords') }}</label>
                                            <textarea class="resize-off form-control" name="meta_keywords"
                                                placeholder="{{translate('Keyword, Keyword')}}"></textarea>
                                            <small class="text-muted">{{ translate('Separate with coma') }}</small>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="col-from-label fs-13">{{ translate('Meta Image') }}</label>
                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                        {{ translate('Browse') }}
                                                    </div>
                                                </div>
                                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                                <input type="hidden" name="meta_img" class="selected-files">
                                            </div>
                                            <div class="file-preview box sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Refund -->
                            @if (addon_is_activated('refund_request'))
                                <div class="card mb-4" id="refund">
                                    <div class="bg-white p-3 p-sm-2rem">
                                        <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                            {{ translate('Refund') }}
                                        </h5>
                                        <div class="w-100">
                                            <div class="form-group row gutters-5">
                                                <label class="col-md-2 col-from-label">{{translate('Refundable')}}?</label>
                                                <div class="col-md-10">
                                                    <label class="aiz-switch aiz-switch-success mb-0 d-block">
                                                        <input type="checkbox" name="refundable" checked value="1"
                                                            onchange="isRefundable()">
                                                        <span></span>
                                                    </label>
                                                    <small id="refundable-note" class="text-muted d-none"></small>
                                                </div>
                                            </div>

                                            <div class="w-100 refund-block d-none">
                                                <div class="form-group row gutters-5">
                                                    <div class="col-md-2"></div>
                                                    <div class="col-md-10">
                                                        <input type="hidden" name="refund_note_id" id="refund_note_id">
                                                        <h5 class="fs-14 fw-600 mb-3 mt-4 pb-3" style="border-bottom: 1px dashed #e4e5eb;">
                                                            {{translate('Refund Note')}}
                                                        </h5>
                                                        <div id="refund_note" class=""></div>
                                                        <button type="button"
                                                            class="btn btn-block border border-dashed hov-bg-soft-secondary mt-2 fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                                            onclick="noteModal('refund')">
                                                            <i class="las la-plus"></i>
                                                            <span class="ml-2">{{ translate('Select Refund Note') }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Warranty -->
                            <div class="card mb-4" id="warranty">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{ translate('Warranty') }}
                                    </h5>
                                    <div class="w-100">
                                        <div class="form-group row gutters-5">
                                            <label class="col-md-2 col-from-label">{{translate('Warranty')}}</label>
                                            <div class="col-md-10">
                                                <label class="aiz-switch aiz-switch-success mb-0">
                                                    <input type="checkbox" name="has_warranty" onchange="warrantySelection()">
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="w-100 warranty_selection_div d-none">
                                            <div class="form-group row">
                                                <div class="col-md-2"></div>
                                                <div class="col-md-10">
                                                    <select class="form-control aiz-selectpicker" name="warranty_id"
                                                        id="warranty_id" data-live-search="true">
                                                        <option value="">{{ translate('Select Warranty') }}</option>
                                                        @foreach (\App\Models\Warranty::all() as $warranty)
                                                            <option value="{{ $warranty->id }}"
                                                                @selected(old('warranty_id') == $warranty->id)>
                                                                {{ $warranty->getTranslation('text') }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <input type="hidden" name="warranty_note_id" id="warranty_note_id">
                                                    <h5 class="fs-14 fw-600 mb-3 mt-4 pb-3" style="border-bottom: 1px dashed #e4e5eb;">
                                                        {{translate('Warranty Note')}}
                                                    </h5>
                                                    <div id="warranty_note" class=""></div>
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
                            </div>

                            <!-- Frequently Bought Products -->
                            <div class="card mb-4" id="frequently_bought">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{ translate('Frequently Bought') }}
                                    </h5>
                                    <div class="w-100">
                                        <div class="d-flex mb-3">
                                            <div class="radio mar-btm mr-4">
                                                <input id="fq_bought_select_products" type="radio"
                                                    name="frequently_bought_selection_type" value="product"
                                                    onchange="fq_bought_product_selection_type()" checked>
                                                <label for="fq_bought_select_products"
                                                    class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Product')}}</label>
                                            </div>
                                            <div class="radio mar-btm">
                                                <input id="fq_bought_select_category" type="radio"
                                                    name="frequently_bought_selection_type" value="category"
                                                    onchange="fq_bought_product_selection_type()">
                                                <label for="fq_bought_select_category"
                                                    class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Category')}}</label>
                                            </div>
                                        </div>

                                        <div class="fq_bought_select_product_div">
                                            <div id="selected-fq-bought-products"></div>
                                            <button type="button"
                                                class="btn btn-block border border-dashed hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                                onclick="showFqBoughtProductModal()">
                                                <i class="las la-plus"></i>
                                                <span class="ml-2">{{ translate('Add More') }}</span>
                                            </button>
                                        </div>

                                        <div class="fq_bought_select_category_div d-none">
                                            <div class="form-group mb-2">
                                                <label class="col-from-label fs-13">{{translate('Category')}}</label>
                                                <select class="form-control aiz-selectpicker"
                                                    data-placeholder="{{ translate('Select a Category')}}"
                                                    name="fq_bought_product_category_id" data-live-search="true">
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}">
                                                            {{ $category->getTranslation('name') }}
                                                        </option>
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

                            <!-- Shipping Configuration -->
                            <div class="card mb-4" id="shipping">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{ translate('Shipping Configuration') }}
                                    </h5>
                                    <div class="w-100">
                                        @if (get_setting('shipping_type') == 'product_wise_shipping')
                                            <div class="form-group row gutters-5 align-items-center">
                                                <label class="col-md-6 col-from-label fs-13">{{ translate('Free Shipping') }}</label>
                                                <div class="col-md-6 text-right">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="radio" name="shipping_type" value="free" checked>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group row gutters-5 align-items-center">
                                                <label class="col-md-6 col-from-label fs-13">{{ translate('Flat Rate') }}</label>
                                                <div class="col-md-6 text-right">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="radio" name="shipping_type" value="flat_rate">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="flat_rate_shipping_div" style="display: none">
                                                <div class="form-group mb-2">
                                                    <label class="col-from-label fs-13">{{ translate('Shipping cost') }}</label>
                                                    <input type="number" lang="en" min="0" value="0" step="0.01"
                                                        placeholder="{{ translate('Shipping cost') }}" name="flat_shipping_cost"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group row gutters-5 align-items-center">
                                                <label class="col-md-8 col-from-label fs-13">{{translate('Is Product Quantity Mulitiply')}}</label>
                                                <div class="col-md-4 text-right">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="checkbox" name="is_quantity_multiplied" value="1">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        @else
                                            <small class="text-muted">{{ translate('Shipping configuration is maintained by Admin.') }}</small>
                                        @endif

                                        <hr class="my-4">

                                        <!-- Estimate Shipping Time -->
                                        <div class="form-group mb-0 mt-3">
                                            <label class="col-from-label fs-13">{{ translate('Estimate Shipping Time (Days)') }}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="est_shipping_days" min="1"
                                                    step="1" placeholder="{{ translate('Shipping Days') }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">{{ translate('Days') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Low Stock Quantity Warning -->
                            <div class="card mb-4" id="low_stock_warning">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{ translate('Low Stock Quantity Warning') }}
                                    </h5>
                                    <div class="w-100">
                                        <div class="form-group mb-0">
                                            <label class="col-from-label fs-13">{{ translate('Quantity') }}</label>
                                            <input type="number" name="low_stock_quantity" value="1" min="0" step="1"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Visibility State -->
                            <div class="card mb-4" id="stock_visibility">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{ translate('Stock Visibility State') }}
                                    </h5>
                                    <div class="w-100">
                                        <div class="form-group row gutters-5 align-items-center">
                                            <label class="col-md-6 col-from-label fs-13">{{ translate('Show Stock Quantity') }}</label>
                                            <div class="col-md-6 text-right">
                                                <label class="aiz-switch aiz-switch-success mb-0">
                                                    <input type="radio" name="stock_visibility_state" value="quantity" checked>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group row gutters-5 align-items-center">
                                            <label class="col-md-6 col-from-label fs-13">{{ translate('Show Stock With Text Only') }}</label>
                                            <div class="col-md-6 text-right">
                                                <label class="aiz-switch aiz-switch-success mb-0">
                                                    <input type="radio" name="stock_visibility_state" value="text">
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group row gutters-5 align-items-center">
                                            <label class="col-md-6 col-from-label fs-13">{{ translate('Hide Stock') }}</label>
                                            <div class="col-md-6 text-right">
                                                <label class="aiz-switch aiz-switch-success mb-0">
                                                    <input type="radio" name="stock_visibility_state" value="hide">
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cash On Delivery -->
                            <div class="card mb-4" id="cod">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{ translate('Cash On Delivery') }}
                                    </h5>
                                    <div class="w-100">
                                        @if (get_setting('cash_payment') == '1')
                                            <div class="form-group row gutters-5 align-items-center">
                                                <label class="col-md-6 col-from-label fs-13">{{ translate('Status') }}</label>
                                                <div class="col-md-6 text-right">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="checkbox" name="cash_on_delivery" value="1" checked="">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        @else
                                            <small class="text-muted">{{ translate('Cash On Delivery activation is maintained by Admin.') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End Advanced Settings Collapse -->
                    </div> <!-- End product-form-sections -->

                    <!-- VAT & Tax Section -->
                    <div class="card mb-4" id="vat_tax">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{translate('VAT & Tax')}}
                            </h5>
                            <div class="w-100">
                                @foreach(\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">
                                        {{$tax->name}}
                                        <input type="hidden" value="{{$tax->id}}" name="tax_id[]">
                                    </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="number" lang="en" min="0" value="0" step="0.01"
                                                placeholder="{{ translate('Tax') }}" name="tax[]" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control aiz-selectpicker" name="tax_type[]">
                                                <option value="amount">{{translate('Flat')}}</option>
                                                <option value="percent">{{translate('Percent')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 mt-4 text-right">
                    <button type="submit" name="button" value="publish"
                        class="btn btn-primary btn-lg min-w-150px fs-15 fw-700 shadow-primary">{{ translate('Upload Product') }}</button>
                </div>
            </div>

        </form>
    </div>
@endsection

@section('modal')
    <!-- Frequently Bought Product Select Modal -->
    @include('modals.product_select_modal')

    {{-- Note Modal --}}
    @include('modals.note_modal')
@endsection

@section('script')
    <!-- Treeview js -->
    <script src="{{ static_asset('assets/js/hummingbird-treeview.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#treeview").hummingbird();

            $('#treeview input:checkbox').on("click", function () {
                let $this = $(this);
                if ($this.prop('checked') && ($('#treeview input:radio:checked').length == 0)) {
                    let val = $this.val();
                    $('#treeview input:radio[value=' + val + ']').prop('checked', true);
                }
            });
        });

        $("[name=shipping_type]").on("change", function () {
            $(".product_wise_shipping_div").hide();
            $(".flat_rate_shipping_div").hide();
            if ($(this).val() == 'product_wise') {
                $(".product_wise_shipping_div").show();
            }
            if ($(this).val() == 'flat_rate') {
                $(".flat_rate_shipping_div").show();
            }

        });

        function add_more_customer_choice_option(i, name) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: '{{ route('seller.products.add-more-choice-option') }}',
                data: {
                    attribute_id: i
                },
                success: function (data) {
                    var obj = JSON.parse(data);
                    $('#customer_choice_options').append('\
                                                                                                        <div class="form-group row">\
                                                                                                            <div class="col-md-3">\
                                                                                                                <input type="hidden" name="choice_no[]" value="' + i + '">\
                                                                                                                <input type="text" class="form-control" name="choice[]" va                                    lue="' + name +
                        '" placeholder="{{ translate('Choice Title') }}" readonly>\
                                                                                                            </div>\
                                                                                                            <div class="col-md-8">\
                                                                                                                <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_' + i + '[]" multiple>\
                                                                                                                    ' + obj + '\
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
                AIZ.plugins.bootstrapSelect('refresh');
            } else {
                $('#colors').prop('disabled', false);
                AIZ.plugins.bootstrapSelect('refresh');
            }
            update_sku();
        });

        $(document).on("change", ".attribute_choice", function () {
            update_sku();
        });

        $('#colors').on('change', function () {
            update_sku();
        });

        $('input[name="unit_price"]').on('keyup', function () {
            update_sku();
        });

        // $('input[name="name"]').on('keyup', function() {
        //     update_sku();
        // });

        function delete_row(em) {
            $(em).closest('.form-group row').remove();
            update_sku();
        }

        function delete_variant(em) {
            $(em).closest('.variant').remove();
        }

        function update_sku() {
            $.ajax({
                type: "POST",
                url: '{{ route('seller.products.sku_combination') }}',
                data: $('#choice_form').serialize(),
                success: function (data) {
                    $('#sku_combination').html(data);
                    AIZ.uploader.previewGenerate();
                    AIZ.plugins.sectionFooTable('#sku_combination');
                    if (data.trim().length > 1) {
                        $('#show-hide-div').hide();

                        // Check availability for all generated variant SKUs
                        $('#sku_combination input[name="sku_combinations[]"]').each(function () {
                            checkSKUAvailability(this);
                        });
                    } else {
                        $('#show-hide-div').show();
                    }
                }
            });
        }

        $('#choice_attributes').on('change', function () {
            $('#customer_choice_options').html(null);
            $.each($("#choice_attributes option:selected"), function () {
                add_more_customer_choice_option($(this).val(), $(this).text());
            });
            update_sku();
        });

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
            $.post('{{ route('seller.product.search') }}', { _token: AIZ.data.csrf, product_id: null, search_key: searchKey, category: fqBroughCategory, product_type: "physical" }, function (data) {
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

        // Warranty
        function warrantySelection() {
            if ($('input[name="has_warranty"]').is(':checked')) {
                $('.warranty_selection_div').removeClass('d-none');
                $('#warranty_id').attr('required', true);
            }
            else {
                $('.warranty_selection_div').addClass('d-none');
                $('#warranty_id').removeAttr('required');
            }
        }

        // Refundable
        function isRefundable() {
            const refundType = "{{ get_setting('refund_type') }}";
            const $refundable = $('input[name="refundable"]');
            const $mainCategoryRadio = $('input[name="category_id"]:checked');
            const $note = $('#refundable-note');

            $refundable.off('change.isRefundableLock');

            if (refundType !== 'category_based_refund') {
                $refundable.prop('disabled', false);
                $note.addClass('d-none');
                $('.refund-block').toggleClass('d-none', !$refundable.is(':checked'));
                return;
            }

            if (!$mainCategoryRadio.length) {
                $refundable.prop('checked', false);
                $refundable.prop('disabled', true);
                $('.refund-block').addClass('d-none');
                $note.text('{{ translate("Your refund type is category based. At first select the main category.") }}')
                    .removeClass('d-none');
                return;
            }

            const categoryId = $mainCategoryRadio.val();
            $.ajax({
                type: 'POST',
                url: '{{ route("seller.products.check_refundable_category") }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    category_id: categoryId
                },
                success: function (response) {
                    if (response.status === 'success' && response.is_refundable) {
                        $refundable.prop('disabled', false);
                        $note.text('{{ translate("This product allows refunds.") }}')
                            .removeClass('d-none');
                        $refundable.on('change.isRefundableLock', function () {
                            if (!$refundable.is(':checked')) {
                                $('.refund-block').addClass('d-none');
                            } else {
                                $('.refund-block').removeClass('d-none');
                            }
                        });
                    } else {
                        $refundable.prop('checked', false);
                        $refundable.prop('disabled', true);
                        $('.refund-block').addClass('d-none');
                        $note.text('{{ translate("Selected main category has no refund. Select a refundable category.") }}')
                            .removeClass('d-none');
                    }
                },
                error: function () {
                    $refundable.prop('checked', false);
                    $refundable.prop('disabled', true);
                    $('.refund-block').addClass('d-none');
                    $note.text('{{ translate("Could not verify category refund status.") }}')
                        .removeClass('d-none');
                }
            });
        }

        function noteModal(noteType) {
            $.post('{{ route('get_notes') }}', { _token: '{{ @csrf_token() }}', note_type: noteType }, function (data) {
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


    </script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            var hash = document.location.hash;
            if (hash) {
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
            } else {
                $('.nav-tabs a[href="#general"]').tab('show');
            }

            $('.nav-tabs a').on('shown.bs.tab', function (e) {
                window.location.hash = e.target.hash;
            });
        });

    </script>

    @include('partials.product.product_temp_data')

    <script type="text/javascript">
        $(document).ready(function () {
            warrantySelection();
            isRefundable();

            $(document).on('change', 'input[name="category_id"]', function () {
                isRefundable();
            });

            $('input[name="refundable"]').on('change', function () {
                if (!$('input[name="refundable"]').prop('disabled')) {
                    $('.refund-block').toggleClass('d-none', !$(this).is(':checked'));
                }
            });
        });

        // HS Code Select2 AJAX autocomplete
        $(document).ready(function () {
            if ($('#hsn_code_select').length) {
                $('#hsn_code_select').select2({
                    placeholder: '{{ translate("Search by code or product name...") }}',
                    allowClear: true,
                    minimumInputLength: 0,
                    dropdownParent: $('.hs-code-select-wrapper').length ? $('.hs-code-select-wrapper') : $('body'),
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



        // AI Generation Handler
        function generateDescriptionAI() {
            if (!$('#product_name').val() || !$('#category_id').val()) {
                AIZ.plugins.notify('danger', '{{ translate("Please enter a Product Name and select a Category first to generate an AI description.") }}');
                return;
            }

            let originalBtnHtml = $('button[onclick="generateDescriptionAI()"]').html();
            $('button[onclick="generateDescriptionAI()"]').html('<i class="las la-spinner la-spin"></i> {{ translate("Generating...") }}').prop('disabled', true);

            $.post('{{ route('seller.ai.generate') }}', {
                _token: '{{ csrf_token() }}',
                product_name: $('#product_name').val(),
                category_id: $('#category_id').val()
            }, function (data) {
                if (data.success) {
                    $('#product_description').val(data.description).siblings('.note-editor').find('.note-editable').html(data.description);
                    $('input[name="meta_title"]').val(data.meta_title);
                    $('textarea[name="meta_description"]').val(data.meta_description);

                    let tags = $('#product_tags').val();
                    let metaKeywords = $('textarea[name="meta_keywords"]');
                    metaKeywords.val(tags).trigger('change');

                    AIZ.plugins.notify('success', '{{ translate("AI Description & SEO Meta Tags generated successfully!") }}');
                    validateField($('input[name="meta_title"]'));
                    validateField($('textarea[name="meta_description"]'));
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

        // Auto-populate tags from Product Name
        $('#product_name').on('change', function () {
            let name = $(this).val();
            if (name) {
                let tagsInput = $('#product_tags');
                let currentTags = tagsInput.val();
                if (!currentTags.includes(name)) {
                    let newTags = currentTags ? currentTags + ',' + name : name;
                    tagsInput.val(newTags);
                    // Also update Meta Keywords if empty
                    if (!$('textarea[name="meta_keywords"]').val()) {
                        $('textarea[name="meta_keywords"]').val(newTags);
                    }
                }
            }
        });

        // Generate SKU
        function generateSKU() {
            let name = $('#product_name').val();
            let prefix = "PRD-";
            if (name && name.trim().length >= 3) {
                // Get first 3 letters or initials
                let words = name.trim().split(/\s+/);
                if (words.length >= 3) {
                    prefix = (words[0][0] + words[1][0] + words[2][0]).toUpperCase() + "-";
                } else {
                    prefix = name.trim().substring(0, 3).toUpperCase() + "-";
                }
            }

            let randomNum = Math.floor(100000 + Math.random() * 900000);
            $('#sku_input').val(prefix + randomNum);
            validateField($('#sku_input'));
            AIZ.plugins.notify('success', '{{ translate("Generated meaningful SKU successfully.") }}');

            if (typeof update_sku === 'function') {
                update_sku();
            }
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

        $(document).on('change', '#sku_input', function () {
            checkSKUAvailability(this);
        });

        $(document).on('change', 'input[name="sku_combinations[]"]', function () {
            checkSKUAvailability(this);
        });

        // Validation Highlighting Logic (Green/Red)
        function validateField(el) {
            if ($(el).val() && $(el).val().length > 0) {
                $(el).addClass('is-valid').removeClass('is-invalid');
            } else {
                $(el).addClass('is-invalid').removeClass('is-valid');
            }
        }

        $(document).ready(function () {
            // Apply instantly on load to highlight empty required fields in red, filled in green
            $('input[required], select[required], textarea[required]').not('.aiz-selectpicker').each(function () {
                validateField(this);
            });

            $('input[required], select[required], textarea[required]').not('.aiz-selectpicker').on('change blur input', function () {
                validateField(this);
            });

            // For Aiz Selectpicker (Bootstrap Select) required fields
            $('.aiz-selectpicker[required]').each(function () {
                if ($(this).val()) {
                    $(this).next('.dropdown-toggle').addClass('border-success').removeClass('border-danger');
                } else {
                    $(this).next('.dropdown-toggle').addClass('border-danger').removeClass('border-success');
                }
            });

            $('.aiz-selectpicker[required]').on('changed.bs.select', function () {
                if ($(this).val()) {
                    $(this).next('.dropdown-toggle').addClass('border-success').removeClass('border-danger');
                } else {
                    $(this).next('.dropdown-toggle').addClass('border-danger').removeClass('border-success');
                }
            });
        });

        // Uploader Modal Tooltips Sequential Display
        $(document).on('shown.bs.modal', '#aizUploaderModal', function () {
            let uploadTip = $('a[href="#aiz-upload-new"]').parent();
            let selectTip = $('a[href="#aiz-select-file"]').parent();

            // Ensure manual trigger behavior doesn't clash
            uploadTip.tooltip({ trigger: 'manual' }).tooltip('show');

            setTimeout(function () {
                uploadTip.tooltip('hide');
                selectTip.tooltip({ trigger: 'manual' }).tooltip('show');

                setTimeout(function () {
                    selectTip.tooltip('hide');
                }, 4000);
            }, 4000);
        });

        // HS Code Select2 AJAX autocomplete
        $(document).ready(function () {
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
                // Restore saved value
                @if(old('hsn_code'))
                    var savedOpt = new Option("{{ old('hsn_code') }}", "{{ old('hsn_code') }}", true, true);
                    $('#hsn_code_select').append(savedOpt).trigger('change');
                @endif
                                                }
        });
    </script>
@endsection