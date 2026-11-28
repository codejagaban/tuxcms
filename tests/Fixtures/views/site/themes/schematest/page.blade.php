{{-- A minimal theme that declares a section type of its own in theme.json.
     Built-in types are drawn by the base views. --}}
<!DOCTYPE html>
<html lang="en">
<head><title>{{ $page->title }}</title></head>
<body>
@foreach ($page->sections as $section)
    @includeFirst(
        ['site.themes.schematest.sections.' . str_replace('_', '-', $section->type), 'site.sections.' . str_replace('_', '-', $section->type), 'site.sections.fallback'],
        ['section' => $section, 'd' => $section->data ?? []]
    )
@endforeach
</body>
</html>
