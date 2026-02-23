<section class="pb-2 py-md-4">
    <div class="container">
      
        <!-- Products Section -->
        <div class="row gutters-16">
            <div class="col-xl-4 col-lg-6 mb-3 mb-lg-0">
                <div class="h-100 w-100 overflow-hidden rounded-2">
                    <a href="{{ route('auction_products.all') }}" class="hov-scale-img">
                        <img class="img-fit lazyload mx-auto h-400px has-transition rounded-75"
                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                            data-src="{{ uploaded_asset(get_setting('auction_banner_image', null, get_system_language()->code)) }}"
                            alt="{{ env('APP_NAME') }} promo"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    </a>
                </div>
            </div>
            @php
                $products = get_auction_products();
            @endphp
            <div class="col-xl-8 col-lg-6">
                <div class="rounded-75 auction-product-box px-3 pt-4 pb-2" style="background: {{ get_setting('auction_product_bg_color', '#C7B198') }}">

                    <div class="d-flex mb-2 align-items-baseline justify-content-between px-0 px-sm-3">
                        <!-- Title -->
                        <h3 class="mb-2 mb-sm-0">
                            <span class="fs-16 fw-700 mb-0">{{ translate('Auction Products') }}</span>
                            <p class="fs-12 mb-0">{{translate('products')}} ({{count($products)}})</p>
                        </h3>
                        <a type="button" class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center" href="{{ route('auction_products.all') }}" >
                            <span><i class="las la-angle-right fs-20 fw-600"></i></span>
                            <span class="fs-12 mr-2 text">View All</span>
                        </a>
                    </div>

                    <div class="aiz-carousel arrow-x-0 arrow-inactive-none" data-items="2" data-xxl-items="2" data-xl-items="2" data-lg-items="1" data-md-items="2" data-sm-items="2" data-xs-items="2"  data-arrows="true" data-dots="false">
                        @php
                            $init = 0 ;
                            $end = 1 ;
                        @endphp
                        @for ($i = 0; $i < 2; $i++)
                            <div class="carousel-box">
                                @foreach ($products as $key => $product)
                                    @if ($key >= $init && $key <= $end)
                                        <div class="position-relative has-transition pb-3">
                                            <div class="row hov-scale-img">
                                                <div class="col-5">
                                                    <a href="{{ route('auction-product', $product->slug) }}" class="d-block rounded-2 overflow-hidden  h-70px w-70px h-sm-90px w-sm-90px h-md-140px w-md-140px text-center text-center">
                                                        <img class="img-fluid h-100 img-fit lazyload mx-auto has-transition"
                                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                            data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                                            alt="{{  $product->getTranslation('name')  }}"
                                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                    </a>
                                                </div>
                                                <div class="col mx-0  py-0 py-sm-2">
                                                    <h3 class="fw-400 fs-14 text-truncate-2 lh-1-4 h-35px mb-2 d-none d-md-block">
                                                        <a href="{{ route('auction-product', $product->slug) }}" class="d-block text-reset hov-text-primary">{{  $product->getTranslation('name')  }}</a>
                                                    </h3>
                                                    <div class="fs-14">
                                                        <span class="text-secondary">{{ translate('Starting Bid') }}</span><br>
                                                        <span class="fw-700 ">{{ single_price($product->starting_bid) }}</span>
                                                    </div>
                                                    @php 
                                                        $highest_bid = $product->bids->max('amount');
                                                        $min_bid_amount = $highest_bid != null ? $highest_bid+1 : $product->starting_bid; 
                                                        $gst_rate = gst_applicable_product_rate($product->id);
                                                    @endphp
                                                    <button class="btn custom-hov-btn text-white custom-bit-btn rounded-1 mt-0 mt-sm-1" onclick="bid_single_modal({{ $product->id }}, {{ $min_bid_amount }},{{ $gst_rate }})" style="background-color: {{ get_setting('auction_product_btn_color') ? get_setting('auction_product_btn_color') : '#C7B198' }};">{{ translate('Place Bid') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            
                                @php
                                    $init += 2;
                                    $end += 2;
                                @endphp
                            </div>
                        @endfor
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>