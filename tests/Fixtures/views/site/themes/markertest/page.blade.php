{{-- A minimal theme for PreviewMarkersTest: one heading and one picture per section. --}}
<!DOCTYPE html>
<html lang="en">
<head><title>{{ $page->title }}</title></head>
<body>
@foreach ($page->sections as $section)
    <section @tuxsection($section)>
        <h2 @field($section, 'title')>{{ $section->title }}</h2>
        <img src="{{ data_get($section->data, 'image') }}" alt="" @image($section, 'data.image')>
    </section>
@endforeach
</body>
</html>
