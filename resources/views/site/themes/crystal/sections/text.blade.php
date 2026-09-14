@php $image = data_get($d, 'image'); @endphp
<section class="page-section" id="{{ $section->key }}">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div @class(['col-lg-7' => $image, 'col-md-10 offset-md-1 col-lg-8 offset-lg-2 text-center' => !$image])>
                @if (data_get($d, 'eyebrow'))<p class="section-caption-fancy mb-20 mb-xs-10">{{ data_get($d, 'eyebrow') }}</p>@endif
                @if ($section->title)<h2 class="section-title-strong mb-30 mb-xs-20 wow fadeInUp">{{ $section->title }}</h2>@endif
                @if ($section->content)<div class="section-descr wow fadeInUp" style="white-space: pre-line">{{ $section->content }}</div>@endif
            </div>
            @if ($image)
                <div class="col-lg-5 mt-md-50"><img src="{{ $image }}" alt="{{ data_get($d, 'image_alt', '') }}" class="w-100 round" loading="lazy"></div>
            @endif
        </div>
    </div>
</section>
