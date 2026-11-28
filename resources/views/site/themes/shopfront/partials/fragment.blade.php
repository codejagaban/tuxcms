@php
    use App\Models\Page;
    use App\Support\Site;

    // A piece of the product drawn into a feature card, chosen by the item's
    // icon. Every piece is built from this site's real, public data: its page
    // tree, the paths a publish writes, this page's sections, its open roles,
    // the photographs its pages show, how this page reads in a search result,
    // or the card's own title under the editor's marker.
    $icon = data_get($item, 'icon');
    $kind = match ($icon) {
        'compass' => 'tree',
        'zap', 'gauge' => 'build',
        'layers' => 'stack',
        'briefcase' => 'jobs',
        'image' => 'media',
        'search' => 'serp',
        default => 'mark',
    };
    $nav = collect($navigation ?? []);
    if (in_array($kind, ['tree', 'build'], true) && $nav->isEmpty()) {
        $kind = 'mark';
    }
    if ($kind === 'jobs') {
        $roles = \App\Models\JobPost::published()->orderByDesc('published_at')->take(3)->get();
        $kind = $roles->isEmpty() ? 'mark' : 'jobs';
    }
    if ($kind === 'media') {
        // Only photographs a published page already shows, never an upload
        // that is still waiting to be used.
        $shown = \App\Models\PageSection::whereIn('page_id', Page::published()->pluck('id'))->pluck('data')->map(fn ($data) => json_encode($data, JSON_UNESCAPED_SLASHES))->implode(' ');
        $photos = \App\Models\MediaLibrary::singleton()->getMedia('library')
            ->filter(fn ($media) => str_contains($shown, (string) parse_url($media->getUrl(), PHP_URL_PATH)))
            ->take(3);
        $kind = $photos->isEmpty() ? 'mark' : 'media';
    }
    $labels = [
        'hero' => 'Hero', 'text' => 'Text', 'features' => 'Features', 'stats' => 'Stats', 'testimonials' => 'Testimonials',
        'team' => 'Team', 'faq' => 'FAQ', 'cta' => 'Call to action', 'contact' => 'Contact', 'contact_form' => 'Contact form', 'map' => 'Map',
    ];
@endphp
<div class="bit bit--{{ $kind }}" aria-hidden="true">
    @switch($kind)
        @case('tree')
            @php
                // Four rows at most, children under their parent, so the
                // tree always fits its panel whole.
                $rows = $nav->flatMap(fn ($node) => [
                    ['label' => $node['label'], 'url' => $node['url'], 'child' => false],
                    ...array_map(fn ($child) => ['label' => $child['label'], 'url' => $child['url'], 'child' => true], $node['children'] ?? []),
                ])->take(4);
            @endphp
            <ul class="bit__tree">
                @foreach ($rows as $row)
                    <li @class(['is-child' => $row['child']])><span class="bit__row"><span class="bit__dot"></span>{{ $row['label'] }}<span class="bit__path data">{{ $row['url'] }}</span></span></li>
                @endforeach
            </ul>
            @break

        @case('build')
            @php
                $paths = $nav->flatMap(fn ($node) => [$node['url'], ...array_column($node['children'] ?? [], 'url')])->take(4);
                $count = Page::published()->count();
            @endphp
            <ol class="bit__log data">
                @foreach ($paths as $path)
                    <li><span class="bit__tick">✓</span>{{ rtrim($path, '/') }}/index.html</li>
                @endforeach
                <li class="bit__done">{{ $count }} {{ $count === 1 ? 'page' : 'pages' }} published</li>
            </ol>
            @break

        @case('stack')
            <ol class="bit__stack">
                @foreach (($sections ?? collect())->take(3) as $n => $part)
                    <li @class(['is-here' => $part->id === $section->id])>
                        <span class="bit__grip"></span>
                        <span class="bit__name">{{ $part->title ?: ($labels[$part->type] ?? ucfirst($part->type)) }}</span>
                        <span class="bit__type">{{ $labels[$part->type] ?? ucfirst($part->type) }}</span>
                    </li>
                @endforeach
            </ol>
            @break

        @case('jobs')
            <ul class="bit__list">
                @foreach ($roles as $role)
                    <li class="bit__row">
                        <span class="bit__what"><span class="bit__name">{{ $role->title }}</span><span class="bit__type">{{ collect([$role->location, $role->job_type])->filter()->implode(', ') }}</span></span>
                        <span class="bit__pill bit__pill--open">Open</span>
                    </li>
                @endforeach
            </ul>
            @break

        @case('media')
            <ul class="bit__list">
                @foreach ($photos as $photo)
                    <li class="bit__row">
                        @include('site.themes.shopfront.partials.glyph', ['name' => 'image', 'class' => 'bit__glyph'])
                        <span class="bit__what"><span class="bit__name">{{ $photo->file_name }}</span><span class="bit__type">{{ $photo->getCustomProperty('alt_text') ?: $photo->name }}</span></span>
                        <span class="bit__path data">{{ number_format($photo->size / 1024) }} KB</span>
                    </li>
                @endforeach
            </ul>
            @break

        @case('serp')
            @php $meta = $page->getMetaTags(); @endphp
            <div class="bit__serp">
                <p class="bit__serp-site"><span><span class="bit__name">{{ Site::name() }}</span><span class="bit__path data">{{ $meta['canonical'] ?? Site::url() }}</span></span></p>
                <p class="bit__serp-title">{{ $meta['title'] ?? $page->title }}</p>
                @if (! empty($meta['description']))
                    <p class="bit__serp-text">{{ $meta['description'] }}</p>
                @endif
            </div>
            @break

        @default
            <div class="bit__page">
                <span class="bit__name-tag">Title</span>
                <p class="bit__edit">{{ data_get($item, 'title') }}<span class="bit__caret"></span></p>
                <span class="bit__line"></span>
                <span class="bit__line bit__line--short"></span>
            </div>
    @endswitch
</div>
