@extends('site.themes.crystal.layout')

@section('content')
    @foreach ($page->sections as $section)
        @continue(! $section->is_visible)

        @includeFirst(
            [
                'site.themes.crystal.sections.'.str_replace('_', '-', $section->type),
                'site.sections.'.str_replace('_', '-', $section->type),
                'site.sections.fallback',
            ],
            ['section' => $section, 'd' => $section->data ?? []]
        )
    @endforeach
@endsection
