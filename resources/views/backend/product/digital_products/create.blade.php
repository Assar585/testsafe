@extends('backend.layouts.app')

@section('content')
    <div class="page-content mx-0">
        <div class="aiz-titlebar text-left mt-2 mb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3">{{ translate('Add New Digital Product') }}</h1>
                </div>
                <div class="col text-right">
                    <a class="btn btn-xs btn-soft-primary" href="javascript:void(0);" onclick="clearTempdata()">
                        {{ translate('Clear Tempdata') }}
                    </a>
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
    
        <form class="form form-horizontal mar-top" action="{{ route('digitalproducts.store') }}" method="POST"
            enctype="multipart/form-data" id="choice_form">
            <div class="row gutters-5">
                <div class="col-lg-8">
                    @csrf
                    <input type="hidden" name="added_by" value="admin">
                    <input type="hidden" name="digital" value="1">
    
                    <div class="card">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{ translate('General') }}
                            </h5>
                            <div class="w-100">
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Product Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="{{ translate('Product Name') }}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Product File') }}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="file_name" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{ translate('Tags') }}</label>
                                    <input type="text" class="form-control aiz-tag-input" name="tags[]"
                                        placeholder="{{ translate('Type to add a tag') }}">
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
                                        <input type="hidden" name="photos" class="selected-files">
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
                                        <input type="hidden" name="thumbnail_img" class="selected-files">
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
                                        placeholder="{{ translate('Meta Title') }}">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Description') }}</label>
                                    <textarea name="meta_description" rows="5" class="form-control"></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Keywords') }}</label>
                                    <textarea class="resize-off form-control" name="meta_keywords" placeholder="{{translate('Keyword, Keyword')}}"></textarea>
                                    <small class="text-muted">{{ translate('Separate with coma') }}</small>                                   
                                </div> 
                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{ translate('Meta Image') }}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="image"
                                        data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ translate('Browse') }}</div>
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
    
                    <div class="card">
                        <div class="bg-white p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">
                                {{ translate('Price') }}
                            </h5>
                            <div class="w-100">
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Unit price') }} <span class="text-danger">*</span></label>
                                    <input type="number" lang="en" min="0" value="0" step="0.01"
                                        placeholder="{{ translate('Unit price') }}" name="unit_price" class="form-control"
                                        required>
                                </div>
                                @if (addon_is_activated('gst_system'))
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('HSN Code')}}</label>
                                        <input type="text" lang="en"  placeholder="{{ translate('HSN Code') }}" name="hsn_code" class="form-control">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="col-from-label fs-13">{{translate('GST Rate (%)')}}</label>
                                        <input type="number" lang="en" min="0" value="0" step="0.01" placeholder="{{ translate('GST Rate') }}" name="gst_rate" class="form-control">
                                    </div>
                                @else
                                    @foreach (\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                                        <div class="form-group mb-3">
                                            <label class="col-from-label fs-13">{{ $tax->name }}</label>
                                            <div class="row gutters-5">
                                                <div class="col-lg-8">
                                                    <input type="hidden" value="{{$tax->id}}" name="tax_id[]">
                                                    <input type="number" lang="en" min="0" value="0" step="0.01"
                                                        placeholder="{{ translate('Tax') }}" name="tax[]" class="form-control"
                                                        required>
                                                </div>
                                                <div class="col-lg-4">
                                                    <select class="form-control aiz-selectpicker" name="tax_type[]">
                                                        <option value="amount">{{ translate('Flat') }}</option>
                                                        <option value="percent">{{ translate('Percent') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="form-group mb-3">
                                    <label class="col-from-label fs-13">{{ translate('Discount Date Range') }}</label>
                                    <input type="text" class="form-control aiz-date-range" name="date_range"
                                        placeholder="{{ translate('Select Date') }}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss"
                                        data-separator=" to " autocomplete="off">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="col-from-label fs-13">{{ translate('Discount') }}</label>
                                    <div class="row gutters-5">
                                        <div class="col-lg-8">
                                            <input type="number" lang="en" min="0" value="0" step="0.01"
                                                placeholder="{{ translate('Discount') }}" name="discount" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-lg-4">
                                            <select class="form-control aiz-selectpicker" name="discount_type">
                                                <option value="amount">{{ translate('Flat') }}</option>
                                                <option value="percent">{{ translate('Percent') }}</option>
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
                                    <label class="col-from-label fs-13">{{ translate('Description') }}</label>
                                    <textarea class="aiz-text-editor" name="description"></textarea>
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
                                    <div class="align-items-center d-flex mar-btm ml-4 mr-5 radio">
                                        <input id="fq_bought_select_products" type="radio" name="frequently_bought_selection_type" value="product" onchange="fq_bought_product_selection_type()" checked >
                                        <label for="fq_bought_select_products" class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Product')}}</label>
                                    </div>
                                    <div class="radio mar-btm mr-3 d-flex align-items-center">
                                        <input id="fq_bought_select_category" type="radio" name="frequently_bought_selection_type" value="category" onchange="fq_bought_product_selection_type()">
                                        <label for="fq_bought_select_category" class="fs-14 fw-500 mb-0 ml-2">{{translate('Select Category')}}</label>
                                    </div>
                                </div>
        
                                <div class="px-0">
                                    <div class="fq_bought_select_product_div">
    
                                        <div id="selected-fq-bought-products">
    
                                        </div>
    
                                        <button 
                                            type="button" 
                                            class="btn btn-block border border-dashed hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                            onclick="showFqBoughtProductModal()">
                                            <i class="las la-plus"></i>
                                            <span class="ml-2">{{ translate('Add More') }}</span>
                                        </button>
                                    </div>
        
                                        <div class="fq_bought_select_category_div d-none">
                                            <div class="form-group mb-0">
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
                                        value="{{ old('category_id') }}">
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
                                                    @selected(old('category_id') == $childCategory->id)>
                                                    &nbsp;&nbsp;&nbsp;-- {{ $childCategory->getTranslation('name') }}
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group mb-0 text-right mb-2">
                        <button type="submit" name="button" value="publish"
                            class="btn btn-primary btn-md fs-13 fw-700 px-5 radius-0">{{translate('Save Product')}}</button>
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
            var searchKey = $('input[name=search_keyword]').val();
            var fqBroughCategory = $('select[name=fq_brough_category]').val();
            $.post('{{ route('product.search') }}', { _token: AIZ.data.csrf,  product_id: null, search_key:searchKey, category:fqBroughCategory, product_type:"digital" }, function(data){
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

    @include('partials.product.product_temp_data')

@endsection
