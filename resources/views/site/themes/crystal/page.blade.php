@php
    $cmsSections = $page->sections->keyBy('key');
    $template = $page->is_homepage ? 'home' : $page->slug;
    $view = 'site.themes.crystal.original.'.$template;
@endphp

@if (view()->exists($view))
    @include($view)
@else
    @include('site.page')
@endif
