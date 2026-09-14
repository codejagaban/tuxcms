@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="page-section bg-gradient-gray-light-2 bg-scroll" id="{{ $section->key }}">
    <div class="container">
        <div class="row mb-60 mb-sm-40"><div class="col-md-8 offset-md-2 text-center"><h2 class="section-title">{{ $section->title }}</h2></div></div>
        <div class="row">
            @foreach ($items as $item)
                <div class="col-md-6 mb-30"><figure class="testimonials-4-item round"><blockquote><p>{{ data_get($item, 'quote') }}</p></blockquote><figcaption class="testimonials-4-author mt-30"><strong>{{ data_get($item, 'author') }}</strong>@if(data_get($item, 'role'))<div class="small">{{ data_get($item, 'role') }}</div>@endif</figcaption></figure></div>
            @endforeach
        </div>
    </div>
</section>
@endif
