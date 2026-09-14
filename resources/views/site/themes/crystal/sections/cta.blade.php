@php $cta = data_get($d, 'primary_cta'); @endphp
<section class="page-section bg-gradient-gray-light-1 bg-scroll light-content">
    <div class="container"><div class="row"><div class="col-md-8 offset-md-2 text-center">
        @if (data_get($d, 'eyebrow'))<p class="section-caption-fancy mb-20 mb-xs-10">{{ data_get($d, 'eyebrow') }}</p>@endif
        @if ($section->title)<h2 class="section-title mb-0">{{ $section->title }}</h2>@endif
        @if ($section->content)<p class="section-descr mt-20">{{ $section->content }}</p>@endif
        @if (data_get($cta, 'label'))<div class="mt-40"><a href="{{ data_get($cta, 'url', '#') }}" class="btn btn-mod btn-color btn-large btn-round btn-hover-anim"><span>{{ data_get($cta, 'label') }}</span></a></div>@endif
    </div></div></div>
</section>
