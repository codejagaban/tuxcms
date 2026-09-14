@php $query = data_get($d, 'query', \App\Models\Setting::getString('contact_address')); @endphp
@if ($query)
<section class="page-section pt-0"><div class="container"><div class="map-boxed"><iframe title="{{ data_get($d, 'label', 'Business location') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="width:100%;height:480px;border:0" src="https://www.google.com/maps?q={{ urlencode($query) }}&output=embed"></iframe></div></div></section>
@endif
