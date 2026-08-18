@extends('site.layout')

@section('content')
    @foreach ($page->sections as $section)
        @continue(! $section->is_visible)

        {{-- Unknown types fall through to the fallback partial, so a section
             type added in the editor never renders as a blank hole. --}}
        @includeFirst(
            ['site.sections.' . str_replace('_', '-', $section->type), 'site.sections.fallback'],
            ['section' => $section, 'd' => $section->data ?? []]
        )
    @endforeach
@endsection
