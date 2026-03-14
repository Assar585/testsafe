@extends('backend.layouts.app')

@section('content')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <div class="page-content">
        <div class="aiz-titlebar text-left mt-2 pb-2 px-3 px-md-2rem border-bottom border-gray">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h3">{{ translate('Edit Product') }}</h1>
                </div>
                {{-- <div class="col text-right">
                    <a class="btn has-transition btn-xs p-0 hov-svg-danger" href="{{ route('home') }}" target="_blank"
                        data-toggle="tooltip" data-placement="top" data-title="{{ translate('View Tutorial Video') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="19.887" height="16" viewBox="0 0 19.887 16">
                            <path id="_42fbab5a39cb8436403668a76e5a774b" data-name="42fbab5a39cb8436403668a76e5a774b"
                                d="M18.723,8H5.5A3.333,3.333,0,0,0,2.17,11.333v9.333A3.333,3.333,0,0,0,5.5,24h13.22a3.333,3.333,0,0,0,3.333-3.333V11.333A3.333,3.333,0,0,0,18.723,8Zm-3.04,8.88-5.47,2.933a1,1,0,0,1-1.473-.88V13.067a1,1,0,0,1,1.473-.88l5.47,2.933a1,1,0,0,1,0,1.76Zm-5.61-3.257L14.5,16l-4.43,2.377Z"
                                transform="translate(-2.17 -8)" fill="#9da3ae" />
                        </svg>
                    </a>
                </div> --}}
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

                <form action="{{route('products.update', $product->id)}}" method="POST" enctype="multipart/form-data"
                    enctype="multipart/form-data" id="aizSubmitForm">
                    @csrf
                    <input name="_method" type="hidden" value="POST">
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input type="hidden" name="tab" id="tab">
                    <input type="hidden" name="type" value="{{ $type }}">

                    <ul class="nav nav-tabs nav-fill language-bar">
                        @foreach (get_all_active_language() as $key => $language)
                            <li class="nav-item">
                                <a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3"
                                    href="{{ route('products.admin.edit', ['id' => $product->id, 'lang' => $language->code]) }}">
                                    <img src="{{ static_asset('assets/img/flags/' . $language->code . '.png') }}" height="11"
                                        class="mr-1">
                                    <span>{{$language->name}}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="product-form-sections">
                        <!-- General -->
                        <div class="card shadow-none border-0 mb-4" id="general">
                            <div class="bg-white p-3 p-sm-2rem">
                                <!-- Product Information -->
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('Product Information')}}
                                </h5>
                                <div class="w-100">
                                    <div class="row">
                                        <div class="col-xxl-6 col-xl-6">
                                            <!-- Product Name -->
                                            <div class="form-group mb-4">
                                                <label class="fs-14 fw-700 text-dark mb-1">{{translate('Product Name')}} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-2 fs-14 @error('name') is-invalid @enderror"
                                                    name="name" placeholder="{{translate('Product Name')}}"
                                                    value="{{ $product->getTranslation('name', $lang) }}">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <!-- Brand -->
                                                    <div class="form-group mb-4" id="brand">
                                                        <label class="fs-14 fw-700 text-dark mb-1">{{translate('Brand')}}</label>
                                                        <select class="form-control aiz-selectpicker rounded-2 fs-14" name="brand_id" id="brand_id"
                                                            data-live-search="true">
                                                            <option value="">{{ translate('Select Brand') }}</option>
                                                            @foreach (\App\Models\Brand::all() as $brand)
                                                                <option value="{{ $brand->id }}" @if($product->brand_id == $brand->id)
                                                                selected @endif>{{ $brand->getTranslation('name') }}</option>
                                                            @endforeach
                                                        </select>
                                                        <small
                                                            class="text-muted fs-12">{{translate("You can choose a brand if you'd like to display your product by brand.")}}</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <!-- Unit -->
                                                    <div class="form-group mb-4">
                                                        <label class="fs-14 fw-700 text-dark mb-1">{{translate('Unit')}} <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" letter-only
                                                            class="form-control rounded-2 fs-14 @error('unit') is-invalid @enderror" name="unit"
                                                            placeholder="{{ translate('Unit (e.g. KG, Pc etc)') }}"
                                                            value="{{$product->getTranslation('unit', $lang)}}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <!-- Weight -->
                                                    <div class="form-group mb-4">
                                                        <label class="fs-14 fw-700 text-dark mb-1">{{translate('Weight')}}
                                                            <small class="fw-400">({{ translate('In Kg') }})</small></label>
                                                        <input type="number" class="form-control rounded-2 fs-14" name="weight"
                                                            value="{{ $product->weight}}" step="0.01" placeholder="0.00">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <!-- Quantity -->
                                                    <div class="form-group mb-4">
                                                        <label class="fs-14 fw-700 text-dark mb-1">{{translate('Minimum Purchase Qty')}}
                                                            <span class="text-danger">*</span></label>
                                                        <input type="number" lang="en"
                                                            class="form-control rounded-2 fs-14 @error('min_qty') is-invalid @enderror"
                                                            name="min_qty"
                                                            value="@if($product->min_qty <= 1){{1}}@else{{$product->min_qty}}@endif"
                                                            min="1" step="1" integer-only required>
                                                        <small
                                                            class="text-muted fs-12">{{translate("The minimum purchase quantity required.")}}</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tags -->
                                            <div class="form-group mb-4">
                                                <label class="fs-14 fw-700 text-dark mb-1">{{translate('Tags')}}</label>
                                                <input type="text" class="form-control aiz-tag-input rounded-2 fs-14" name="tags[]"
                                                    id="tags" value="{{ $product->tags }}"
                                                    placeholder="{{ translate('Type to add a tag') }}"
                                                    data-role="tagsinput">
                                                <small
                                                    class="text-muted fs-12">{{translate('Used for search. Separate with enter/comma.')}}</small>
                                            </div>

                                            @if (addon_is_activated('pos_system'))
                                                <!-- Barcode -->
                                                <div class="form-group mb-4">
                                                    <label class="fs-14 fw-700 text-dark mb-1">{{translate('Barcode')}}</label>
                                                    <input type="text" class="form-control rounded-2 fs-14" name="barcode"
                                                        placeholder="{{ translate('Barcode') }}"
                                                        value="{{ $product->barcode }}">
                                                </div>
                                            @endif
                                        </div>

                                        <div class="col-xxl-6 col-xl-6">
                                            <!-- Product Category -->
                                            <div class="form-group mb-4">
                                                <label class="fs-14 fw-700 text-dark mb-1">{{translate('Product Category')}} <span
                                                        class="text-danger">*</span></label>
                                                <input type="hidden" name="category_ids[]" id="category_ids_hidden"
                                                    value="{{ $product->category_id }}">
                                                <select
                                                    class="form-control aiz-selectpicker rounded-2 fs-14 @error('category_id') is-invalid @enderror"
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

                                            <!-- HS Code -->
                                            <div class="form-group mb-4">
                                                <label class="fs-14 fw-700 text-dark mb-1">{{translate('TN VED (HS Code)')}}</label>
                                                <select class="form-control hsn-select2 rounded-2 fs-14" name="hsn_code" id="hsn_code_select">
                                                    @if($product->hsn_code)
                                                        <option value="{{ $product->hsn_code }}" selected>
                                                            {{ $product->hsn_code_name ?? $product->hsn_code }}
                                                        </option>
                                                    @endif
                                                </select>
                                                <small
                                                    class="text-muted fs-12">{{ translate('Used for international shipping and customs.') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="fs-14 fw-700 text-dark mb-0">{{translate('Description')}}</label>
                                            <button type="button" class="btn btn-sm btn-soft-primary rounded-2 px-3"
                                                onclick="generateDescriptionAI()">
                                                <i class="las la-magic"></i> {{translate('Generate by AI')}}
                                            </button>
                                        </div>
                                        <textarea class="aiz-text-editor"
                                            name="description">{{ $product->getTranslation('description', $lang) }}</textarea>
                                    </div>
                                </div>


                                <!-- Refund -->
                                @if (addon_is_activated('refund_request'))
                                    <h5 class="mb-3 mt-5 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{translate('Refund')}}
                                    </h5>
                                    <div class="w-100">
                                        <!-- Refundable -->
                                        <div class="form-group row">
                                            <label class="col-md-3 col-from-label">{{translate('Refundable')}}?</label>
                                            <div class="col-md-9">
                                                <label class="aiz-switch aiz-switch-success mb-0 d-block">
                                                    <input type="checkbox" name="refundable" @if ($product->refundable == 1)
                                                    checked @endif value="1" onchange="isRefundable()">
                                                    <span></span>
                                                </label>
                                                <small id="refundable-note" class="text-muted d-none"></small>
                                            </div>
                                        </div>
                                        <div class="w-100 refund-block @if($product->refundable != 1) d-none @endif">

                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <label class="form-check-label fw-bold" for="flexCheckChecked">
                                                        <b>{{translate('Note (Add from preset)')}} </b>
                                                    </label>
                                                </div>
                                            </div>

                                            <input type="hidden" name="refund_note_id" id="refund_note_id"
                                                value="{{ $product->refund_note_id }}">
                                            <div id="refund_note">
                                                @if($product->refundNote != null)
                                                    <div class="border border-gray my-2 p-2">
                                                        {{ $product->refundNote->getTranslation('description') ?? '' }}
                                                    </div>
                                                @endif
                                            </div>

                                            <button type="button"
                                                class="btn btn-block border border-dashed hov-bg-soft-secondary mt-2 fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                                onclick="noteModal('refund')">
                                                <i class="las la-plus"></i>
                                                <span class="ml-2">{{ translate('Select Refund Note') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <!-- Status -->
                                <h5 class="mb-3 mt-5 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('Status')}}
                                </h5>
                                <div class="w-100">
                                    <!-- Featured -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">{{translate('Featured')}}</label>
                                        <div class="col-md-9">
                                            <label class="aiz-switch aiz-switch-success mb-0 d-block">
                                                <input type="checkbox" name="featured" value="1" @if($product->featured == 1)
                                                checked @endif>
                                                <span></span>
                                            </label>
                                            <small
                                                class="text-muted">{{ translate('If you enable this, this product will be granted as a featured product.') }}</small>
                                        </div>
                                    </div>
                                    <!-- Todays Deal -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">{{translate('Todays Deal')}}</label>
                                        <div class="col-md-9">
                                            <label class="aiz-switch aiz-switch-success mb-0 d-block">
                                                <input type="checkbox" name="todays_deal" value="1"
                                                    @if($product->todays_deal == 1) checked @endif>
                                                <span></span>
                                            </label>
                                            <small
                                                class="text-muted">{{ translate('If you enable this, this product will be granted as a todays deal product.') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Flash Deal -->
                                <h5 class="mb-3 mt-4 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('Flash Deal')}}
                                    <small
                                        class="text-muted">({{ translate('If you want to select this product as a flash deal, you can use it') }})</small>
                                </h5>
                                <div class="w-100">
                                    <!-- Add To Flash -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">{{translate('Add To Flash')}}</label>
                                        <div class="col-xxl-9">
                                            @php
                                                $productFlashDealId = $product->flash_deal_products->last()->flash_deal_id ?? null;
                                            @endphp
                                            <select class="form-control aiz-selectpicker" name="flash_deal_id"
                                                id="video_provider">
                                                <option value="">{{ translate('Choose Flash Title') }}</option>
                                                @foreach(\App\Models\FlashDeal::where("status", 1)->get() as $flash_deal)
                                                    <option value="{{ $flash_deal->id }}"
                                                        @if($productFlashDealId == $flash_deal->id) selected @endif>
                                                        {{ $flash_deal->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- Discount -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">{{translate('Discount')}}</label>
                                        <div class="col-xxl-9">
                                            <input type="number" name="flash_discount" value="{{ $product->discount }}"
                                                min="0" step="0.01" class="form-control">
                                        </div>
                                    </div>
                                    <!-- Discount Type -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">{{translate('Discount Type')}}</label>
                                        <div class="col-xxl-9">
                                            <select class="form-control aiz-selectpicker" name="flash_discount_type" id="">
                                                <option value="">{{ translate('Choose Discount Type') }}</option>
                                                <option value="amount" @if($product->discount_type == 'amount') selected
                                                @endif>
                                                    {{translate('Flat')}}
                                                </option>
                                                <option value="percent" @if($product->discount_type == 'percent') selected
                                                @endif>
                                                    {{translate('Percent')}}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- GST Rate -->
                                @if (addon_is_activated('gst_system'))
                                    <h5 class="mb-3 mt-4 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                        {{translate('TN VED & GST')}}
                                    </h5>
                                    <div class="w-100">
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">{{translate('TN VED (HS Code)')}}</label>
                                            <select class="form-control" name="hsn_code" id="hsn_code_select">
                                                <option value="{{ $product->hsn_code }}" selected>{{ $product->hsn_code }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">{{translate('GST Rate (%)')}}</label>
                                            <input type="number" lang="en" min="0" value="{{ $product->gst_rate }}" step="0.01"
                                                placeholder="{{ translate('GST Rate') }}" name="gst_rate" class="form-control">
                                        </div>
                                    </div>
                                @else
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
                                                        <option value="amount" @if($tax_type == 'amount') selected @endif>
                                                            {{translate('Flat')}}
                                                        </option>
                                                        <option value="percent" @if($tax_type == 'percent') selected @endif>
                                                            {{translate('Percent')}}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Files & Media -->
                        <div class="card shadow-none border-0 mb-4" id="files_and_media">
                            <div class="bg-white p-3 p-sm-2rem">
                                <!-- Product Files & Media -->
                                <h5 class="mb-4 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
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
                                            class="text-muted">{{ translate('This image is visible in all product box. Minimum dimensions required: 195px width X 195px height. Keep some blank space around main object of your image as we had to crop some edge in different devices to make it responsive.  If no thumbnail is uploaded, the products first gallery image will be used as the thumbnail image.') }}</small>
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
                                                    <small
                                                        class="text-muted">{{ translate("Use proper link without extra parameter. Don't use short share link/embeded iframe code.") }}</small>
                                                </div>

                                            </div>
                                        @endif

                                        @foreach ($product->video_link ?? [] as $index => $video_link)
                                            @if($index == 0)

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control" name="video_link[]"
                                                            value="{{ $video_link }}" placeholder="{{ translate('Video Link') }}">
                                                        <small
                                                            class="text-muted">{{ translate("Use proper link without extra parameter. Don't use short share link/embeded iframe code.") }}</small>
                                                    </div>

                                                </div>
                                            @else
                                                <div class="row">
                                                    <div class="col-md-11">
                                                        <input type="text" class="form-control" name="video_link[]"
                                                            value="{{ $video_link }}" placeholder="{{ translate('Video Link') }}">
                                                        <small
                                                            class="text-muted">{{ translate("Use proper link without extra parameter. Don't use short share link/embeded iframe code.") }}</small>
                                                    </div>
                                                    <div class="col-1 d-flex justify-content-end">
                                                        <button type="button" class="mt-1 btn btn-icon  btn-sm btn-soft-danger"
                                                            data-toggle="remove-parent" data-parent=".row">
                                                            <i class="las la-times"></i>
                                                        </button>
                                                    </div>

                                                </div>
                                            @endif
                                        @endforeach
                                    </div>


                                    <div class="form-group row d-flex justify-content-end " style="width: 100%">
                                        <button type="button"
                                            class="btn btn-block border border-dashed hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center ml-3 mt-3"
                                            data-toggle="add-more" data-content='<div class="row mb-2">
                                                                                                            <div class="col">
                                                                                                                <input type="text" class="form-control" name="video_link[]" value="" placeholder="{{ translate('Youtube video or short link') }}">
                                                                                                                <small class="text-muted">{{ translate("Use proper link without extra parameter. Don't use short share link/embeded iframe code.") }}</small>
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
                        <div class="card shadow-none border-0 mb-4" id="price_and_stocks">
                            <div class="bg-white p-3 p-sm-2rem">
                                <!-- tab Title -->
                                <h5 class="mb-4 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
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
                                            <select class="form-control aiz-selectpicker" data-live-search="true"
                                                data-selected-text-format="count" name="colors[]" id="colors" multiple <?php if (count(json_decode($product->colors)) < 1)
        echo "disabled"; ?>>
                                                @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                                                                        <option value="{{ $color->code }}"
                                                                                            data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>"
                                                                                            <?php    if (in_array($color->code, json_decode($product->colors)))
                                                    echo 'selected' ?>></option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input value="1" type="checkbox" name="colors_active" <?php if (count(json_decode($product->colors)) > 0)
        echo "checked"; ?>>
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
                                            <select name="choice_attributes[]" id="choice_attributes"
                                                data-selected-text-format="count" data-live-search="true"
                                                class="form-control aiz-selectpicker" multiple
                                                data-placeholder="{{ translate('Choose Attributes') }}">
                                                @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                                    <option value="{{ $attribute->id }}" @if($product->attributes != null && in_array($attribute->id, json_decode($product->attributes, true)))
                                                    selected @endif>{{ $attribute->getTranslation('name') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <p>{{ translate('Choose the attributes of this product and then input values of each attribute') }}
                                        </p>
                                        <br>
                                    </div>

                                    <!-- choice options -->
                                    <div class="customer_choice_options" id="customer_choice_options">
                                        @foreach (json_decode($product->choice_options) as $key => $choice_option)
                                            <div class="form-group row">
                                                <div class="col-lg-3">
                                                    <input type="hidden" name="choice_no[]"
                                                        value="{{ $choice_option->attribute_id }}">
                                                    <input type="text" class="form-control" name="choice[]"
                                                        value="{{ optional(\App\Models\Attribute::find($choice_option->attribute_id))->getTranslation('name') }}"
                                                        placeholder="{{ translate('Choice Title') }}" disabled>
                                                </div>
                                                <div class="col-lg-8">
                                                    <select class="form-control aiz-selectpicker attribute_choice"
                                                        data-live-search="true"
                                                        name="choice_options_{{ $choice_option->attribute_id }}[]"
                                                        data-selected-text-format="count" multiple required>
                                                        @foreach (\App\Models\AttributeValue::where('attribute_id', $choice_option->attribute_id)->get() as $row)
                                                            <option value="{{ $row->value }}" @if(in_array($row->value, $choice_option->values)) selected @endif>
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
                                        $start_date = date('d-m-Y H:i:s', $product->discount_start_date);
                                        $end_date = date('d-m-Y H:i:s', $product->discount_end_date);
                                    @endphp
                                    <!-- Discount Date Range -->
                                    <div class="form-group mb-2">
                                        <label class="control-label"
                                            for="start_date">{{translate('Discount Date Range')}}</label>
                                        <input type="text" class="form-control aiz-date-range"
                                            @if($product->discount_start_date && $product->discount_end_date)
                                            value="{{ $start_date . ' to ' . $end_date }}" @endif name="date_range"
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
                                                    <option value="amount" <?php if ($product->discount_type == 'amount')
        echo "selected"; ?>>{{translate('Flat')}}</option>
                                                    <option value="percent" <?php if ($product->discount_type == 'percent')
        echo "selected"; ?>>{{translate('Percent')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    @if(addon_is_activated('club_point'))
                                        <!-- club point -->
                                        <div class="form-group mb-2">
                                            <label class="col-from-label">
                                                {{translate('Set Point')}}
                                            </label>
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
                                    <!-- External link -->
                                    <div class="form-group mb-2">
                                        <label class="col-from-label">
                                            {{translate('External link')}}
                                        </label>
                                        <input type="text" placeholder="{{ translate('External link') }}"
                                            name="external_link" value="{{ $product->external_link }}" class="form-control">
                                        <small
                                            class="text-muted">{{translate('Leave it blank if you do not use external site link')}}</small>
                                    </div>
                                    <!-- External link button text -->
                                    <div class="form-group mb-2">
                                        <label class="col-from-label">
                                            {{translate('External link button text')}}
                                        </label>
                                        <input type="text" placeholder="{{ translate('External link button text') }}"
                                            name="external_link_btn" value="{{ $product->external_link_btn }}"
                                            class="form-control">
                                        <small
                                            class="text-muted">{{translate('Leave it blank if you do not use external site link')}}</small>
                                    </div>
                                    <br>
                                    <!-- sku combination -->
                                    <div class="sku_combination" id="sku_combination">

                                    </div>
                                </div>

                                <!-- Low Stock Quantity -->
                                <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                    {{translate('Low Stock Quantity Warning')}}
                                </h5>
                                <div class="w-100 mb-3">
                                    <div class="form-group mb-2">
                                        <label class="col-from-label">
                                            {{translate('Quantity')}}
                                        </label>
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
                                                    @if($product->stock_visibility_state == 'quantity') checked @endif>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Show Stock With Text Only -->
                                    <div class="form-group row">
                                        <label
                                            class="col-md-3 col-from-label">{{translate('Show Stock With Text Only')}}</label>
                                        <div class="col-md-9">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="radio" name="stock_visibility_state" value="text"
                                                    @if($product->stock_visibility_state == 'text') checked @endif>
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
                                                    @if($product->stock_visibility_state == 'hide') checked @endif>
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
                                    <!-- tab Title -->
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
                                            <label class="col-form-label"
                                                for="signinSrEmail">{{ translate('Meta Image') }}</label>
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
                                            <div class="file-preview box sm">
                                            </div>
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
                                    <!-- Shipping Configuration -->
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
                                                            @if($product->cash_on_delivery == 1) checked @endif>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        @else
                                            <p>
                                                {{ translate('Cash On Delivery option is disabled. Activate this feature from here') }}
                                                <a href="{{route('activation.index')}}"
                                                    class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index', 'shipping_configuration.edit', 'shipping_configuration.update'])}}">
                                                    <span
                                                        class="aiz-side-nav-text">{{translate('Cash Payment Activation')}}</span>
                                                </a>
                                            </p>
                                        @endif

                                        @if (get_setting('shipping_type') == 'product_wise_shipping')
                                            <!-- Free Shipping -->
                                            <div class="form-group row">
                                                <label class="col-md-3 col-from-label">{{translate('Free Shipping')}}</label>
                                                <div class="col-md-9">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="radio" name="shipping_type" value="free"
                                                            @if($product->shipping_type == 'free') checked @endif>
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
                                                            @if($product->shipping_type == 'flat_rate') checked @endif>
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
                                                <label
                                                    class="col-md-3 col-from-label">{{translate('Is Product Quantity Mulitiply')}}</label>
                                                <div class="col-md-9">
                                                    <label class="aiz-switch aiz-switch-success mb-0">
                                                        <input type="checkbox" name="is_quantity_multiplied" value="1"
                                                            @if($product->is_quantity_multiplied == 1) checked @endif>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        @else
                                            <p>
                                                {{ translate('Product wise shipping cost is disable. Shipping cost is configured from here') }}
                                                <a href="{{route('shipping_configuration.shipping_method')}}"
                                                    class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.shipping_method'])}}">
                                                    <span class="aiz-side-nav-text">{{translate('Shipping Method')}}</span>
                                                </a>
                                            </p>
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
                                                    <span class="input-group-text"
                                                        id="inputGroupPrepend">{{translate('Days')}}</span>
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
                                                    @if($product->has_warranty == 1) checked @endif>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div
                                        class="w-100 warranty_selection_div @if($product->has_warranty != 1) d-none @endif">
                                        <div class="form-group row">
                                            <div class="col-md-2"></div>
                                            <div class="col-md-10">
                                                <select class="form-control aiz-selectpicker" name="warranty_id"
                                                    id="warranty_id" data-selected="{{ $product->warranty_id }}"
                                                    data-live-search="true" @if($product->has_warranty == 1) required @endif>
                                                    <option value="">{{ translate('Select Warranty') }}</option>
                                                    @foreach (\App\Models\Warranty::all() as $warranty)
                                                        <option value="{{ $warranty->id }}"
                                                            @selected(old('warranty_id') == $warranty->id)>
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

                            <!-- Frequently Bought Product -->
                            <div class="card mb-4" id="frequenty-bought-product">
                                <div class="bg-white p-3 p-sm-2rem">
                                    <!-- tab Title -->
                                    <h5 class="mb-3 pb-3 fs-17 fw-700">{{translate('Frequently Bought')}}</h5>
                                    <div class="w-100">
                                        <div class="d-flex mb-4">
                                            <div class="radio mar-btm mr-5 d-flex align-items-center">
                                                <input id="fq_bought_select_products" type="radio"
                                                    name="frequently_bought_selection_type" value="product"
                                                    onchange="fq_bought_product_selection_type()"
                                                    @if($product->frequently_bought_selection_type == 'product') checked
                                                    @endif>
                                                <label for="fq_bought_select_products"
                                                    class="fs-14 fw-700 mb-0 ml-2">{{translate('Select Product')}}</label>
                                            </div>
                                            <div class="radio mar-btm mr-3 d-flex align-items-center">
                                                <input id="fq_bought_select_category" type="radio"
                                                    name="frequently_bought_selection_type" value="category"
                                                    onchange="fq_bought_product_selection_type()"
                                                    @if($product->frequently_bought_selection_type == 'category') checked
                                                    @endif>
                                                <label for="fq_bought_select_category"
                                                    class="fs-14 fw-700 mb-0 ml-2">{{translate('Select Category')}}</label>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-body">
                                                <div class="fq_bought_select_product_div d-none">
                                                    @php
                                                        $fq_bought_products = $product->frequently_bought_products()->where('category_id', null)->get();
                                                    @endphp

                                                    <div id="selected-fq-bought-products">
                                                        @if(count($fq_bought_products) > 0)
                                                            <div class="table-responsive mb-4">
                                                                <table class="table mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="opacity-50 pl-0">
                                                                                {{ translate('Product Thumb') }}
                                                                            </th>
                                                                            <th class="opacity-50">
                                                                                {{ translate('Product Name') }}
                                                                            </th>
                                                                            <th class="opacity-50">{{ translate('Category') }}
                                                                            </th>
                                                                            <th class="opacity-50 text-right pr-0">
                                                                                {{ translate('Options') }}
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($fq_bought_products as $fQBproduct)
                                                                            <tr class="remove-parent">
                                                                                <input type="hidden" name="fq_bought_product_ids[]"
                                                                                    value="{{ $fQBproduct->frequently_bought_product->id }}">
                                                                                <td class="w-150px pl-0"
                                                                                    style="vertical-align: middle;">
                                                                                    <p class="d-block size-48px">
                                                                                        <img src="{{ uploaded_asset($fQBproduct->frequently_bought_product->thumbnail_img) }}"
                                                                                            alt="{{ translate('Image')}}"
                                                                                            class="h-100 img-fit lazyload"
                                                                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                                                    </p>
                                                                                </td>
                                                                                <td style="vertical-align: middle;">
                                                                                    <p class="d-block fs-13 fw-700 hov-text-primary mb-1 text-dark"
                                                                                        title="{{ translate('Product Name') }}">
                                                                                        {{ $fQBproduct->frequently_bought_product->getTranslation('name') }}
                                                                                    </p>
                                                                                </td>
                                                                                <td style="vertical-align: middle;">
                                                                                    {{ $fQBproduct->frequently_bought_product->main_category->name ?? translate('Category Not Found') }}
                                                                                </td>
                                                                                <td class="text-right pr-0"
                                                                                    style="vertical-align: middle;">
                                                                                    <button type="button"
                                                                                        class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                                                        data-toggle="remove-parent"
                                                                                        data-parent=".remove-parent">
                                                                                        <i class="las la-trash"></i>
                                                                                    </button>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <button type="button"
                                                        class="btn btn-block border border-dashed hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                                        onclick="showFqBoughtProductModal()">
                                                        <i class="las la-plus"></i>
                                                        <span class="ml-2">{{ translate('Add More') }}</span>
                                                    </button>
                                                </div>

                                                {{-- Select Category for Frequently Bought Product --}}
                                                <div class="fq_bought_select_category_div d-none">
                                                    @php
                                                        $fq_bought_product_category_id = $product->frequently_bought_products()->where('category_id', '!=', null)->first();
                                                        $fqCategory = $fq_bought_product_category_id != null ? $fq_bought_product_category_id->category_id : null;

                                                    @endphp
                                                    <div class="form-group row">
                                                        <label class="col-md-2 col-from-label">{{translate('Category')}}
                                                            <span class="text-danger">*</span></label>
                                                        <div class="col-md-10">
                                                            <select class="form-control aiz-selectpicker"
                                                                data-placeholder="{{ translate('Select a Category')}}"
                                                                name="fq_bought_product_category_id" data-live-search="true"
                                                                data-selected="{{ $fqCategory }}" required>
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
                                    </div>
                                </div>
                            </div>

                        </div> <!-- End Advanced Settings Collapse -->

                    </div>

                    <!-- Update Button -->
                    <div class="mt-4 text-right">
                        @if ($product->draft)
                            <button type="submit" name="button" value="unpublish" data-action="unpublish"
                                class="mx-2 btn btn-light w-230px btn-md rounded-2 fs-14 fw-700 shadow-secondary border-soft-secondary action-btn">{{ translate('Save & Unpublish') }}</button>
                            <button type="submit" name="button" value="publish" data-action="publish"
                                class="mx-2 btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success action-btn">{{ translate('Save & Publish') }}</button>
                            <button type="button" name="button" value="draft"
                                class="mx-2 btn btn-secondary w-230px btn-md rounded-2 fs-14 fw-700 shadow-secondary action-btn"
                                id="saveDraftBtn">{{ translate('Save as Draft') }}</button>
                        @else
                            <button type="submit" name="button"
                                class="mx-2 btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success action-btn">{{ translate('Update') }}</button>
                        @endif
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
    <!-- Treeview js -->
    <script src="{{ static_asset('assets/js/hummingbird-treeview.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(fu                    nction() {
            show_hide_shipping_div();



            fq_bought_product_selection_type();

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
                url: '{{ route('products.add-more-choice-option') }}',
                data: {
                    attribute_id: i
                },
                success: function (data) {
                    var obj = JSON.parse(data);
                    $('#customer_choice_options').append('\
                                                                            <div class="form-group row">\
                                                                                <div class="col-md-3">\
                                                                                    <input type="hidden" name="choice_no[]" value="'+ i + '">\
                                                                                    <input type="text" class="form-control" name="choice[]" value="'+ name + '" placeholder="{{ translate('Choice Title') }}" readonly>\
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
                AIZ.plugins.bootstrapSelect('refresh');
            }
            else {
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

        function delete_row(em) {
            $(em).closest('.form-group').remove();
            update_sku();
        }

        function delete_variant(em) {
            $(em).closest('.variant').remove();
        }

        function update_sku() {
            $.ajax({
                type: "POST",
                url: '{{ route('products.sku_combination_edit') }}',
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

                        // Check availability for all generated variant SKUs
                        $('#sku_combination input[name="sku_combinations[]"]').each(function () {
                            checkSKUAvailability(this);
                        });
                    }
                    else {
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

            var str = @php echo $product->attributes @endphp;

            $.each(str, function (index, value) {
                flag = false;
                $.each($("#choice_attributes option:selected"), function (j, attribute) {
                    if (value == $(attribute).val()) {
                        flag = true;
                    }
                });
                if (!flag) {
                    $('input[name="choice_no[]"][value="' + value + '"]').parent().parent().remove();
                }
            });

            update_sku();
        });

        function fq_bought_product_selection_type() {
            var productSelectionType = $("input[name='frequently_bought_selection_type']:checked").val();
            if (productSelectionType == 'product') {
                $('.fq_bought_select_product_div').removeClass('d-none');
                $('.fq_bought_select_category_div').addClass('d-none');
                $("select[name='fq_bought_product_category_id']").prop('required', false);
            }
            else if (productSelectionType == 'category') {
                $('.fq_bought_select_category_div').removeClass('d-none');
                $('.fq_bought_select_product_div').addClass('d-none');
                $("select[name='fq_bought_product_category_id']").prop('required', true);
            }
        }

        function showFqBoughtProductModal() {
            $('#fq-bought-product-select-modal').modal('show', { backdrop: 'static' });
        }

        function filterFqBoughtProduct() {
            var productID = $('input[name=id]').val();
            var searchKey = $('input[name=search_keyword]').val();
            var fqBroughCategory = $('select[name=fq_brough_category]').val();
            $.post('{{ route('product.search') }}', { _token: AIZ.data.csrf, product_id: productID, search_key: searchKey, category: fqBroughCategory, product_type: "physical" }, function (data) {
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

            $.post('{{ route('get-selected-products') }}', { _token: AIZ.data.csrf, product_ids: productIds }, function (data) {
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

            if (!refundType) {
                $refundable.prop('checked', false);
                $refundable.prop('disabled', true);
                $('.refund-block').addClass('d-none');
                $note.text('{{ translate("Refund system is not configured.") }}')
                    .removeClass('d-none');
                return;
            }

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
                url: '{{ route("admin.products.check_refundable_category") }}',
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
    <script>
        $(document).ready(function () {
            var hash = document.location.hash;
            if (hash) {
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
                $('#tab').val(location.hash.substr(1));
            } else {
                $('.nav-tabs a[href="#general"]').tab('show');
                $('#tab').val('general');
            }

            // Change hash for page-reload
            $('.nav-tabs a').on('shown.bs.tab', function (e) {
                window.location.hash = e.target.hash;
            });
        });

    </script>

    <script type="text/javascript">
        // innitially assign pid null
        let draftProductId = null;
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

            function saveDraft() {
                let form = $('#aizSubmitForm')[0];
                let formData = new FormData(form);

                // Update Draft
                if (draftProductId) {
                    formData.append('id', draftProductId);
                }
                let draftBtn = $('#saveDraftBtn');
                let draftBtnText = draftBtn.length ? draftBtn.text() : '';
                if (draftBtn.length) {
                    draftBtn.prop('disabled', true).html('<i class="las la-spinner la-spin mr-2"></i> ' + AIZ.local.saving_as_draft);
                }

                $.ajax({
                    url: "{{ route('products.store_as_draft') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            draftProductId = response.product_id;

                            // Update form action for future edits
                            $('#aizSubmitForm').attr('action', "{{ url('admin/products/update') }}/" + draftProductId);

                            if ($('#aizSubmitForm input[name="_method"]').length === 0) {
                                $('#aizSubmitForm').append('<input type="hidden" name="_method" value="POST">');
                            }
                            if (draftBtn.length) {
                                draftBtn.prop('disabled', false).html('<i class="las la-check-circle mr-2"></i>' + draftBtnText);
                            }
                            AIZ.plugins.notify('success', `${response.message}`);
                        } else {
                            if (draftBtn.length) {
                                draftBtn.prop('disabled', false).html('<i class="las la-exclamation-circle text-danger mr-2"></i>' + draftBtnText);
                            }
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            Object.values(errors).forEach(function (fieldErrors) {
                                fieldErrors.forEach(function (error) {
                                    // AIZ.plugins.notify('danger', error);
                                });
                            });
                            if (draftBtn.length) {
                                draftBtn.prop('disabled', false).html('<i class="las la-exclamation-circle text-danger mr-2"></i>' + draftBtnText);
                            }
                        } else {
                            // AIZ.plugins.notify('danger', AIZ.local.error_occured_while_processing);
                            if (draftBtn.length) {
                                draftBtn.prop('disabled', false).html('<i class="las la-exclamation-circle text-danger mr-2"></i>' + draftBtnText);
                            }
                        }
                    }
                });
            }

            // Auto-save on tab click
            $('a[data-toggle="tab"]').on('show.bs.tab', function () {
                var productName = $('input[name="name"]').val();
                if (productName && productName.trim() !== '') {
                    var idDraft = {{ $product->draft}};
                    if (idDraft == 1) {
                        saveDraft();
                    }
                }

            });

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

            $('#saveDraftBtn').on('click', function (e) {
                e.preventDefault();
                saveDraft();
            });

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
        });

        // HS Code Select2 AJAX autocomplete
        $(document).ready(function () {
            if ($('#hsn_code_select').length) {
                $('#hsn_code_select').select2({
                    placeholder: '{{ translate("Search by code or product name...") }}',
                    allowClear: true,
                    minimumInputLength: 0,
                    ajax: {
                        url: '{{ route("products.hs_code_search") }}',
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
    </script>

@endsection