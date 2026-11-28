@php
    use App\Support\Site;

    $posting = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => $job->title,
        'description' => $job->summary,
        'datePosted' => $job->published_at?->toDateString(),
        'validThrough' => $job->closes_at?->endOfDay()->toAtomString(),
        'employmentType' => strtoupper(str_replace('-', '_', (string) ($job->employment_type ?: $job->job_type))) ?: null,
        'hiringOrganization' => ['@type' => 'Organization', 'name' => Site::name(), 'sameAs' => Site::url()],
        'jobLocation' => $job->location
            ? ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $job->location]]
            : null,
    ]);
    $facts = array_filter(['Location' => $job->location, 'Job type' => $job->job_type, 'Hours' => $job->hours, 'Salary' => $job->salary, 'Closes' => $job->closes_at?->format('j F Y')]);
    $lists = ['responsibilities' => 'Key responsibilities', 'essential' => 'Essential skills and experience', 'desirable' => 'Desirable', 'benefits' => 'What we offer'];
@endphp
@extends('site.themes.shopfront.layout', [
    'headTitle' => $job->title . ' | ' . Site::name(),
    'headDescription' => $job->summary,
])

@push('head')
    {{-- JSON_HEX_TAG makes a </script> breakout impossible. --}}
    <script type="application/ld+json">{!! json_encode($posting, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <section class="hero hero--page">
        @include('site.themes.shopfront.partials.sky')
        <div class="wrap">
            <div class="hero__grid">
                <h1 class="display hero__title hero__title--{{ mb_strlen($job->title) <= 16 ? 'short' : (mb_strlen($job->title) <= 36 ? 'medium' : 'long') }}">{{ $job->title }}</h1>
                <div class="hero__aside">
                    @if (filled($job->summary))
                        <div class="hero__say"><p>{{ $job->summary }}</p></div>
                    @endif
                    <div class="hero__do"><a href="#apply" class="button"><span>Apply for this role</span>@include('site.themes.shopfront.partials.arrow')</a></div>
                </div>
            </div>
        </div>
    </section>

    <article class="block job">
        <div class="wrap">
            <a class="back" href="/careers/">@include('site.themes.shopfront.partials.arrow')<span>All roles</span></a>

            @if ($facts)
                <dl class="facts">
                    @foreach ($facts as $label => $value)
                        <div><dt>@include('site.themes.shopfront.partials.glyph', ['name' => ['Location' => 'pin', 'Job type' => 'briefcase', 'Hours' => 'clock', 'Salary' => 'coin', 'Closes' => 'calendar'][$label] ?? 'box', 'class' => 'facts__glyph']){{ $label }}</dt><dd>{{ $value }}</dd></div>
                    @endforeach
                </dl>
            @endif

            @if (filled($job->description))
                <h2>The role</h2>
                <div class="prose">
                    @include('site.themes.shopfront.partials.paragraphs', ['text' => $job->description])
                </div>
            @endif

            @foreach ($lists as $field => $heading)
                @if (count($job->{$field} ?? []))
                    <h2>{{ $heading }}</h2>
                    <ul>
                        @foreach ($job->{$field} as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif
            @endforeach

            <section id="apply">
                <h2>Apply for this role</h2>
                <p>Complete the form and attach your CV. It goes straight to the hiring team.@if ($job->closes_at) Applications close {{ $job->closes_at->format('j F Y') }}.@endif</p>

                <form class="form form--apply" action="/api/v1/job-applications" method="post" enctype="multipart/form-data" data-job-application>
                    <input type="hidden" name="job" value="{{ $job->slug }}">
                    <input type="hidden" name="form_token" value="">
                    <label class="form__trap" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>

                    <div class="form__pair">
                        <div class="field"><label for="apply-name">Full name</label><input id="apply-name" name="name" required autocomplete="name"></div>
                        <div class="field"><label for="apply-email">Email address</label><input id="apply-email" type="email" name="email" required autocomplete="email"></div>
                    </div>
                    <div class="field"><label for="apply-phone">Phone number <small>(optional)</small></label><input id="apply-phone" name="phone" autocomplete="tel"></div>
                    <div class="field"><label for="apply-letter">Cover letter</label><textarea id="apply-letter" name="cover_letter" rows="8" required></textarea></div>
                    <div class="field"><label for="apply-cv">CV <small>PDF, DOC or DOCX, up to 5 MB</small></label><input id="apply-cv" type="file" name="cv" accept=".pdf,.doc,.docx" required></div>

                    <div><button type="submit" class="button"><span>Submit application</span>@include('site.themes.shopfront.partials.arrow')</button></div>
                    <p class="form__result" role="status" aria-live="polite"></p>
                </form>
            </section>
        </div>
    </article>
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.querySelector('[data-job-application]');
            if (!form) return;
            var result = form.querySelector('.form__result');

            // Proof the form page was opened, and when. The API holds back
            // anything sent without it.
            fetch('/api/v1/form-token', { headers: { Accept: 'application/json' } })
                .then(function (response) { return response.json(); })
                .then(function (json) { form.elements.form_token.value = json.token || ''; })
                .catch(function () {});

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var button = form.querySelector('button[type="submit"]');
                button.disabled = true;
                result.textContent = 'Sending your application';

                fetch(form.action, { method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(form) })
                    .then(function (response) {
                        return response.json().then(function (json) {
                            if (!response.ok) {
                                var first = Object.values(json.errors || {}).flat()[0];
                                throw new Error(first || json.message || 'Please check the form and try again.');
                            }
                            form.reset();
                            result.textContent = json.message + ' Reference: ' + json.reference + '.';
                        });
                    })
                    .catch(function (error) { result.textContent = error.message || 'Your application could not be sent. Please try again.'; })
                    .finally(function () { button.disabled = false; });
            });
        })();
    </script>
@endpush
