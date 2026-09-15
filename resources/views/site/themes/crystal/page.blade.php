@php
    $cmsSections = $page->sections->keyBy('key');
    $template = $page->is_homepage ? 'home' : $page->slug;
    $view = 'site.themes.crystal.original.'.$template;
    $storedContent = json_decode($page->content ?: '{}', true);
    $crystalOverrides = is_array($storedContent) ? ($storedContent['crystal_overrides'] ?? []) : [];
@endphp

@if (view()->exists($view))
    @include($view)
    @if (! empty($crystalOverrides))
        <script data-crystal-overrides>
          (() => {
            const overrides = @json($crystalOverrides, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            const normalise = (value = '') => value.replace(/\s+/g, ' ').trim();
            const text = [...document.querySelectorAll('main h1, main h2, main h3, main h4, main h5, main h6, main p, main blockquote, main .features-list-text, main .alt-features-descr, main a span')];
            Object.entries(overrides.text || {}).forEach(([index, value]) => {
              if (text[index] && normalise(value).length > 1) text[index].textContent = value;
            });
            const images = [...document.querySelectorAll('main img')].filter((image) => {
              const source = new URL(image.src, document.baseURI).pathname;
              return normalise(image.alt) && !/(decoration|bg-shape|logo|favicon)/i.test(source);
            });
            Object.entries(overrides.images || {}).forEach(([index, replacement]) => {
              if (!images[index] || !replacement?.url) return;
              images[index].src = replacement.url;
              if (replacement.alt) images[index].alt = replacement.alt;
            });
          })();
        </script>
    @endif
@else
    @include('site.page')
@endif
