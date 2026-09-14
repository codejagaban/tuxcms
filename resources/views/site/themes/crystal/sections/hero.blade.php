@php
    $primary = data_get($d, 'primary_cta');
    $secondary = data_get($d, 'secondary_cta');
    $image = data_get($d, 'image', '/themes/crystal/images/intro/hero.png');
    $caption = data_get($d, 'subheading');
@endphp
<section class="home-section bg-gradient-gray-light-2" id="home">
    <div class="bg-shape-1 wow fadeIn"><img src="/themes/crystal/images/demo-fancy/bg-shape-1.svg" alt=""></div>
    <div class="container position-relative min-height-100vh d-flex align-items-center pt-100 pb-100 pt-sm-120 pb-sm-120">
        <div class="home-content text-start w-100">
            <div class="row">
                <div class="col-md-10 offset-md-1 col-lg-6 offset-lg-0 col-xl-5 d-flex align-items-center mb-md-60 mb-sm-30">
                    <div class="w-100 text-center text-lg-start">
                        @if ($caption)<p class="section-caption-fancy mb-30 mb-xs-20 wow fadeInUp">{{ $caption }}</p>@endif
                        @if ($section->title)<h1 class="hs-title-10 mb-30"><span class="wow charsAnimIn" data-splitting="chars">{{ $section->title }}</span></h1>@endif
                        @if ($section->content)<p class="section-descr mb-40 wow fadeInUp">{{ $section->content }}</p>@endif
                        <div class="local-scroll wow fadeInUp wch-unset">
                            @if (data_get($primary, 'label'))
                                <a href="{{ data_get($primary, 'url', '#') }}" class="btn btn-mod btn-color btn-large btn-round btn-hover-anim me-1 mb-xs-10"><span>{{ data_get($primary, 'label') }}</span></a>
                            @endif
                            @if (data_get($secondary, 'label'))
                                <a href="{{ data_get($secondary, 'url', '#') }}" class="btn btn-mod btn-border-c btn-large btn-round mb-xs-10">{{ data_get($secondary, 'label') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                @if ($image)
                    <div class="col-lg-6 col-xl-7 d-flex align-items-center">
                        <div class="w-100 wow fadeInLeft"><div class="position-relative mt-40 mb-20"><img src="{{ $image }}" alt="{{ data_get($d, 'image_alt', $section->title) }}" class="w-100"></div></div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
