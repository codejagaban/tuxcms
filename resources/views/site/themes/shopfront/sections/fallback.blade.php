{{-- A safe home for section types with no view of their own. --}}
@if (filled($section->title) || filled($section->content))
    @include('site.themes.shopfront.sections.text')
@endif
