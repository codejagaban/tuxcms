@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="page-section"><div class="container"><div class="row text-center">
    @foreach ($items as $item)<div class="col-6 col-md-3 mb-sm-30"><div class="number-1">{{ data_get($item, 'value') }}</div><div class="number-title">{{ data_get($item, 'label') }}</div></div>@endforeach
</div></div></section>
@endif
