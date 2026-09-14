@php
    use App\Support\Site;
    $contact = Site::contact();
@endphp
<section class="page-section" id="{{ $section->key }}">
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-5 mb-md-50">
                @if (data_get($d, 'eyebrow'))<p class="section-caption-fancy mb-20 mb-xs-10">{{ data_get($d, 'eyebrow') }}</p>@endif
                @if ($section->title)<h2 class="section-title mb-40">{{ $section->title }}</h2>@endif
                @if ($section->content)<p class="section-descr mb-40">{{ $section->content }}</p>@endif
                @if (!empty($contact['address']))<div class="contact-item mb-30"><div class="ci-icon"><i class="mi-location"></i></div><div class="ci-text">{{ $contact['address'] }}</div></div>@endif
                @if (!empty($contact['email']))<div class="contact-item mb-30"><div class="ci-icon"><i class="mi-email"></i></div><div class="ci-text"><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></div></div>@endif
                @if (!empty($contact['phone']))<div class="contact-item"><div class="ci-icon"><i class="mi-mobile"></i></div><div class="ci-text"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></div></div>@endif
            </div>
            <div class="col-lg-7">
                @if (data_get($d, 'image'))<img src="{{ data_get($d, 'image') }}" alt="{{ data_get($d, 'image_alt', '') }}" class="w-100 round" loading="lazy">@endif
            </div>
        </div>
    </div>
</section>
