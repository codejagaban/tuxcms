@extends('site.themes.shopfront.layout')

@section('content')
    @php $visible = $page->sections->where('is_visible', true)->values(); @endphp
    @foreach ($visible as $i => $section)
        {{-- Each section sits in a part of its own: an anchor the editor's
             section list can jump to, and a hook for where it falls on the
             page. A section type with no view here falls through to the
             fallback, so a type added in the editor never renders as a hole. --}}
        <div id="part-{{ $section->id }}" @class([
            'part',
            'part--' . str_replace('_', '-', $section->type),
            'part--front' => $i === 0 && $section->type === 'hero' && ($page->is_homepage ?? false),
        ])>
            @includeFirst(
                ['site.themes.shopfront.sections.' . str_replace('_', '-', $section->type), 'site.themes.shopfront.sections.fallback'],
                ['section' => $section, 'd' => $section->data ?? [], 'sections' => $visible]
            )
        </div>
    @endforeach
@endsection
