@php
    $cmsSections = $page->sections->keyBy('key');
    $template = $page->is_homepage ? 'home' : $page->slug;
    $view = 'site.themes.crystal.original.'.$template;
@endphp

{{-- A hand-built page prints its sections' data, including its `copy`
     section for the copy written into the template. Any other page renders
     section by section. --}}
@if (view()->exists($view))
    @include($view)
@else
    @include('site.page')
@endif
