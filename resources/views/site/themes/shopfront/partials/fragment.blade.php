@php
    use App\Models\Page;
    use App\Support\Site;

    // A piece of the product drawn into a feature card, chosen by the item's
    // icon. Every piece is built from this site's real, public data: its page
    // tree, the paths a publish writes, this page's sections, or the card's
    // own title under the editor's marker.
    $icon = data_get($item, 'icon');
    $kind = match ($icon) {
        'compass' => 'tree',
        'zap', 'gauge' => 'build',
        'layers' => 'stack',
        default => 'mark',
    };
    $nav = collect($navigation ?? []);
    if (in_array($kind, ['tree', 'build'], true) && $nav->isEmpty()) {
        $kind = 'mark';
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

        @default
            <div class="bit__page">
                <span class="bit__name-tag">Title</span>
                <p class="bit__edit">{{ data_get($item, 'title') }}<span class="bit__caret"></span></p>
                <span class="bit__line"></span>
                <span class="bit__line bit__line--short"></span>
            </div>
    @endswitch
</div>
