<!-- Last Viewed Products  -->
@if(get_setting('last_viewed_product_activation') == 1 && Auth::check() && auth()->user()->user_type == 'customer')
    <div class="border-top" id="section_last_viewed_products" style="background-color: #fcfcfc;">
        <div class="container container-fixed px-1 px-md-3">
            <h4 class="fs-16 fw-700 py-3 mb-0">{{ translate('Last Viewed Products') }}</h4>
            <div class="aiz-carousel aiz-carousel-fixed gutters-10 half-outside-arrow" data-items="6" data-xl-items="5"
                data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                @foreach (getLastViewedProducts() as $key => $product)
                    <div class="carousel-box">
                        <div class="aiz-card-box hov-shadow-sm border border-light rounded hov-scale-img ml-1 mb-1 shadow-none">
                            <div class="position-relative h-140px h-md-200px img-fit opacity-100">
                                <a href="{{ route('product', $product->product->slug) }}" class="d-block h-100">
                                    <img class="lazyload mx-auto h-100" src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                        data-src="{{ uploaded_asset($product->product->thumbnail_img) }}"
                                        alt="{{  $product->product->getTranslation('name')  }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                </a>
                            </div>
                            <div class="p-md-3 p-2 text-left">
                                <div class="fs-15">
                                    <span class="fw-700 text-primary">{{ home_discounted_base_price($product->product) }}</span>
                                </div>
                                <h3 class="fw-400 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px mt-1">
                                    <a href="{{ route('product', ['slug' => $product->product->slug]) }}"
                                        class="d-block text-reset hov-text-primary">{{  $product->product->getTranslation('name')  }}</a>
                                </h3>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<footer class="pt-3 pb-3 pb-lg-0 text-light" style="background-color: #111119 !important;">
    <div class="container">
        <div class="row no-gutters-lg">
            <div class="col-lg-3 col-md-6 col-sm-6 text-center text-sm-left">
                <div class="mt-4">
                    <a href="{{ route('home') }}" class="d-block">
                        @if(get_setting('footer_logo') != null)
                            <img src="{{ uploaded_asset(get_setting('footer_logo')) }}" alt="{{ env('APP_NAME') }}"
                                height="44">
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" height="44">
                        @endif
                    </a>
                    <div class="my-3 text-soft-light fs-13 lh-1-8 text-break" style="max-width: 250px;">
                        {!! get_setting('about_us_description', null, App::getLocale()) !!}
                    </div>
                    <ul class="list-inline mb-0">
                        @if (!empty(get_setting('facebook_link')))
                            <li class="list-inline-item">
                                <a href="{{ get_setting('facebook_link') }}" target="_blank" class="facebook"><i
                                        class="lab la-facebook-f"></i></a>
                            </li>
                        @endif
                        @if (!empty(get_setting('twitter_link')))
                            <li class="list-inline-item">
                                <a href="{{ get_setting('twitter_link') }}" target="_blank" class="x-twitter">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        viewBox="0 0 16 16">
                                        <path
                                            d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z" />
                                    </svg>
                                </a>
                            </li>
                        @endif
                        @if (!empty(get_setting('instagram_link')))
                            <li class="list-inline-item">
                                <a href="{{ get_setting('instagram_link') }}" target="_blank" class="instagram"><i
                                        class="lab la-instagram"></i></a>
                            </li>
                        @endif
                        @if (!empty(get_setting('youtube_link')))
                            <li class="list-inline-item">
                                <a href="{{ get_setting('youtube_link') }}" target="_blank" class="youtube"><i
                                        class="lab la-youtube"></i></a>
                            </li>
                        @endif
                        @if (!empty(get_setting('linkedin_link')))
                            <li class="list-inline-item">
                                <a href="{{ get_setting('linkedin_link') }}" target="_blank" class="linkedin"><i
                                        class="lab la-linkedin-in"></i></a>
                            </li>
                        @endif

                        {{-- Custom Social Icons from Settings --}}
                        @if (get_setting('custom_social_links') != null)
                            @php
                                $custom_links = json_decode(get_setting('custom_social_links'), true);
                                $custom_images = json_decode(get_setting('custom_social_images'), true);
                            @endphp
                            @if (is_array($custom_links) && is_array($custom_images))
                                @foreach ($custom_links as $index => $link)
                                    @if (!empty($link) && isset($custom_images[$index]))
                                        <li class="list-inline-item">
                                            <a href="{{ $link }}" target="_blank" class="has-img">
                                                <img src="{{ uploaded_asset($custom_images[$index]) }}" alt="Social Icon">
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                        @endif
                    </ul>
                </div>
            </div>
            @php die('FOOTER_SECTION_1_DONE'); @endphp