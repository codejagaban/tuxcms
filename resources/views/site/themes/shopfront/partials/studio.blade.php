@php
    use App\Models\Page;
    use App\Support\Site;

    $labels = [
        'hero' => 'Hero', 'text' => 'Text', 'features' => 'Features', 'stats' => 'Stats', 'testimonials' => 'Testimonials',
        'team' => 'Team', 'faq' => 'FAQ', 'cta' => 'Call to action', 'contact' => 'Contact', 'contact_form' => 'Contact form', 'map' => 'Map',
    ];
    $published = Page::published()->count();
    $lead = trim(preg_split('/\n\s*\n/', trim((string) $hero->content))[0] ?? '');
    $menu = collect($navigation ?? [])->pluck('label')->take(4);
@endphp
{{-- A working copy of the TuxCMS editor, holding this page: its real title,
     its real sections and the site's real menu. The words in the preview can
     be clicked and typed over, the device buttons resize it, and Publish runs
     a build. Nothing here is saved: it is the editor shown, not the editor. --}}
<div class="studio" data-studio data-pages="{{ $published }}">
    <div class="studio__bar">
        <svg class="studio__back" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M13 8H3M7 4L3 8l4 4"/></svg>
        <div class="studio__doc">
            <p class="studio__page">{{ $page->title }}</p>
            <p class="studio__path data">/</p>
        </div>
        <p class="studio__state" data-state>
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="8" cy="8" r="6"/><path d="M5.5 8.2l1.7 1.7 3.3-3.6"/></svg>
            <span data-state-text>Published</span>
        </p>
        <div class="studio__views" role="group" aria-label="Preview width">
            <button type="button" class="studio__view" data-view="desk" aria-pressed="true" aria-label="Desktop">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2" y="3" width="12" height="8" rx="1.2"/><path d="M6 13.5h4"/></svg>
            </button>
            <button type="button" class="studio__view" data-view="phone" aria-pressed="false" aria-label="Phone">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="5" y="2" width="6" height="12" rx="1.4"/><path d="M7.4 12h1.2"/></svg>
            </button>
        </div>
        <button type="button" class="studio__publish" data-publish>
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="8" cy="8" r="6"/><path d="M2 8h12M8 2c1.8 2 1.8 10 0 12M8 2c-1.8 2-1.8 10 0 12"/></svg>
            <span data-publish-text>Publish</span>
        </button>
    </div>

    <div class="studio__body">
        <div class="studio__stage">
            <div class="studio__canvas" data-canvas>
                <div class="mini__bar">
                    <span class="mini__name">{{ Site::name() }}</span>
                    <span class="mini__menu">
                        @foreach ($menu as $label)
                            <span>{{ $label }}</span>
                        @endforeach
                    </span>
                </div>
                <div class="mini__hero">
                    <p class="mini__title" data-edit data-label="Title">{{ $hero->title }}</p>
                    @if ($lead !== '')
                        <p class="mini__say" data-edit data-label="Content">{{ $lead }}</p>
                    @endif
                    @if (filled(data_get($hero->data, 'primary_cta.label')))
                        <span class="mini__button"><span data-edit data-label="Button">{{ data_get($hero->data, 'primary_cta.label') }}</span></span>
                    @endif
                </div>
            </div>
        </div>

        <div class="studio__panel">
            <p class="studio__head">Sections <span class="data">{{ $sections->count() }}</span></p>
            <ol class="studio__sections">
                @foreach ($sections as $n => $part)
                    <li>
                        <a href="#part-{{ $part->id }}" @class(['is-here' => $part->id === $hero->id])>
                            <span class="studio__n data">{{ $n + 1 }}</span>
                            <span class="studio__what">
                                <span class="studio__title">{{ $part->title ?: ($labels[$part->type] ?? ucfirst($part->type)) }}</span>
                                <span class="studio__type">{{ $labels[$part->type] ?? ucfirst($part->type) }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    <p class="studio__log" data-log aria-live="polite">{{ $published }} {{ $published === 1 ? 'page' : 'pages' }} published</p>
</div>
