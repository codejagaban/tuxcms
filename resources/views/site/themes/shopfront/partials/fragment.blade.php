@php
    use App\Models\Page;
    use App\Support\Site;

    // A piece of the product drawn into a feature card, chosen by the item's
    // icon. Every piece is built from this site's real, public data or the
    // product's own wording: its page tree, the paths a publish writes, this
    // page's sections, its open roles, the photographs its pages show, how
    // this page reads in a search result, the spam filter's real verdicts,
    // the roles people sign in with, the site's settings, its contact form,
    // this page's structured data, the publish panel, the "Add section" menu,
    // or, for any other icon, the card's own title under the editor's marker.
    $icon = data_get($item, 'icon');
    $kind = match ($icon) {
        'compass' => 'tree',
        'zap', 'gauge' => 'build',
        'layers' => 'stack',
        'briefcase' => 'jobs',
        'image' => 'media',
        'search' => 'serp',
        'mail' => 'inbox',
        'shield' => 'held',
        'users' => 'roles',
        'star' => 'settings',
        'pen-tool' => 'form',
        'code' => 'schema',
        'calendar', 'clock' => 'schedule',
        'sparkles' => 'menu',
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
    if ($kind === 'roles') {
        $people = \Spatie\Permission\Models\Role::withCount('permissions')->orderByDesc('permissions_count')->take(4)->get();
        $kind = $people->isEmpty() ? 'mark' : 'roles';
    }
    if ($kind === 'form') {
        // The fields of the site's own contact form, as an editor set them.
        $asks = collect(data_get(\App\Models\PageSection::where('type', 'contact_form')->whereIn('page_id', Page::published()->pluck('id'))->value('data'), 'fields', []))->take(3);
        $kind = $asks->isEmpty() ? 'mark' : 'form';
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

        @case('inbox')
            {{-- The spam filter's three outcomes, in its own words. --}}
            <ul class="bit__list">
                <li class="bit__row"><span class="bit__what"><span class="bit__name">Emailed to you</span><span class="bit__type">Contact form</span></span><span class="bit__pill bit__pill--open">Sent</span></li>
                <li class="bit__row"><span class="bit__what"><span class="bit__name">Possible sales pitch: mentions seo</span><span class="bit__type">Contact form</span></span><span class="bit__pill bit__pill--look">Flagged</span></li>
                <li class="bit__row"><span class="bit__what"><span class="bit__name">Sent faster than a person could fill in the form</span><span class="bit__type">Job application</span></span><span class="bit__pill bit__pill--held">Held</span></li>
            </ul>
            @break

        @case('held')
            <div class="bit__held">
                <p class="bit__held-top"><span class="bit__pill bit__pill--held">Held</span><span class="bit__type">Not emailed</span></p>
                <p class="bit__held-why">Sales pitch for website redesign ("free audit")</p>
                <p class="bit__held-act"><span class="bit__button">Release</span><span class="bit__type">Kept with its reason</span></p>
            </div>
            @break

        @case('roles')
            <ul class="bit__list">
                @foreach ($people as $role)
                    <li class="bit__row">
                        <span class="bit__what"><span class="bit__name">{{ ucfirst($role->name) }}</span><span class="bit__type">{{ $role->permissions_count }} {{ $role->permissions_count === 1 ? 'permission' : 'permissions' }}</span></span>
                        <span class="bit__bar" style="--fill: {{ round(100 * $role->permissions_count / max(1, $people->max('permissions_count'))) }}%"></span>
                    </li>
                @endforeach
            </ul>
            @break

        @case('settings')
            @php
                $values = collect([
                    'Site name' => Site::name(),
                    'Contact email' => \App\Models\Setting::getString('contact_email'),
                    'Phone' => \App\Models\Setting::getString('contact_phone'),
                ])->filter();
            @endphp
            <dl class="bit__fields">
                @foreach ($values as $label => $value)
                    <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                @endforeach
            </dl>
            @break

        @case('form')
            <div class="bit__fields bit__fields--form">
                @foreach ($asks as $ask)
                    <div><dt>{{ data_get($ask, 'label') }}</dt><dd>{{ data_get($ask, 'placeholder') ?: ' ' }}</dd></div>
                @endforeach
                <span class="bit__button">Send</span>
            </div>
            @break

        @case('schema')
            <pre class="bit__code data">{"@type": "WebPage",
 "name": "{{ $page->title }}",
 "isPartOf": {
   "@type": "WebSite",
   "name": "{{ Site::name() }}"
 }}</pre>
            @break

        @case('schedule')
            <div class="bit__fields">
                <p class="bit__seg"><span>Publish now</span><span class="is-on">On a date</span></p>
                <div><dt>Goes live</dt><dd class="data">{{ now()->addWeek()->startOfDay()->addHours(9)->format('D j M Y, H:i') }}</dd></div>
                <p class="bit__held-top"><span class="bit__pill bit__pill--look">Scheduled</span><span class="bit__type">Publishes by itself</span></p>
            </div>
            @break

        @case('menu')
            @php $types = collect(\App\Support\SectionTypes::schema(\App\Support\Site::pageTheme($page->template ?? null)))->reverse()->take(4)->reverse(); @endphp
            <ul class="bit__list">
                @foreach ($types as $type)
                    <li class="bit__row"><span class="bit__what"><span class="bit__name">{{ $type['label'] }}</span><span class="bit__type">{{ $type['blurb'] }}</span></span>@if ($type['source'] === 'theme')<span class="bit__pill bit__pill--look">Theme</span>@endif</li>
                @endforeach
            </ul>
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
