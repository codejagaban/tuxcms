@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="page-section bg-gradient-gray-light-1 bg-scroll light-content" id="{{ $section->key }}">
    <div class="container position-relative">
        <div class="row mb-60 mb-sm-40">
            <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2 text-center">
                @if (data_get($d, 'eyebrow'))<p class="section-caption-fancy mb-20 mb-xs-10">{{ data_get($d, 'eyebrow') }}</p>@endif
                @if ($section->title)<h2 class="section-title mb-0 mb-sm-20 wow fadeInUp">{{ $section->title }}</h2>@endif
                @if ($section->content)<p class="section-descr mt-20">{{ $section->content }}</p>@endif
            </div>
        </div>
        <div class="row">
            @foreach ($items as $item)
                <article class="col-md-4 mb-40 text-center wow fadeInUp">
                    @if (data_get($item, 'image'))<img src="{{ data_get($item, 'image') }}" alt="{{ data_get($item, 'title') }}" class="w-100 mb-20" loading="lazy">@endif
                    <h3 class="h4 mb-10">{{ data_get($item, 'title') }}</h3>
                    <p class="text-gray mb-20">{{ data_get($item, 'description') }}</p>
                    @if (data_get($item, 'url'))<a href="{{ data_get($item, 'url') }}" class="link-hover-anim">{{ data_get($item, 'link_label', 'Learn more') }} <i class="mi-arrow-right size-18"></i></a>@endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
