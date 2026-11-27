@extends('site.themes.shopfront.layout')

@section('content')
    @foreach ($page->sections as $section)
        @continue(! $section->is_visible)

        {{-- A section type with no view here falls through to the fallback, so
             a type added in the editor never renders as a hole. --}}
        @includeFirst(
            ['site.themes.shopfront.sections.' . str_replace('_', '-', $section->type), 'site.themes.shopfront.sections.fallback'],
            ['section' => $section, 'd' => $section->data ?? []]
        )
    @endforeach
@endsection
