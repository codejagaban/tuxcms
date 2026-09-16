<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $job->title }} — Crystal Services Limited</title><meta name="description" content="{{ $job->summary }}">
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org', '@type' => 'JobPosting', 'title' => $job->title,
        'description' => $job->summary, 'datePosted' => $job->published_at?->toDateString(),
        'validThrough' => $job->closes_at?->endOfDay()->toAtomString(),
        'employmentType' => strtoupper(str_replace('-', '_', $job->employment_type ?: $job->job_type)),
        'hiringOrganization' => ['@type' => 'Organization', 'name' => \App\Support\Site::name(), 'sameAs' => \App\Support\Site::url()],
        'jobLocation' => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $job->location]],
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <link rel="icon" href="/themes/crystal/images/logo/favicon.png" type="image/png">
    <link rel="stylesheet" href="/themes/crystal/css/bootstrap.min.css">
    <link rel="stylesheet" href="/themes/crystal/css/style.css?v=3">
    <link rel="stylesheet" href="/themes/crystal/css/style-responsive.css">
    <link rel="stylesheet" href="/themes/crystal/css/vertical-rhythm.min.css">
    <link rel="stylesheet" href="/themes/crystal/css/demo-fancy/demo-fancy.css?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <style>
        body{background:#fff;color:#17213b}.job-shell{max-width:1120px;margin:auto;padding:145px 28px 100px}.job-back{display:inline-block;margin-bottom:42px;color:#536079}.job-hero{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:72px;padding-bottom:56px;border-bottom:1px solid #d9deea}.job-hero h1{font-size:clamp(44px,6vw,76px);line-height:1;letter-spacing:-.055em;margin:0 0 24px}.job-summary{font-size:20px;line-height:1.65;color:#536079;max-width:700px}.facts{margin:0}.facts div{padding:13px 0;border-bottom:1px solid #e4e7ef}.facts dt{font-size:12px;color:#707a90}.facts dd{margin:4px 0 0;font-weight:700}.job-body{max-width:760px;padding-top:58px}.job-body h2{font-size:29px;margin:48px 0 18px;letter-spacing:-.025em}.job-body p,.job-body li{font-size:17px;line-height:1.75;color:#3f4b63}.job-body ul{padding-left:22px}.apply{margin-top:58px;padding-top:38px;border-top:1px solid #d9deea}.application-form{display:grid;gap:18px;margin-top:28px}.application-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.application-form label{display:grid;gap:7px;font-weight:600}.application-form input,.application-form textarea{width:100%;border:1px solid #b8c0d0;border-radius:4px;padding:13px 14px;color:#17213b;background:#fff}.application-form input:focus,.application-form textarea:focus{outline:3px solid rgba(36,88,220,.25);border-color:#2458dc}.application-form button{justify-self:start;border:0;border-radius:4px;padding:15px 22px;background:#17213b;color:#fff;font-weight:700}.application-form button:disabled{opacity:.55}.application-result{min-height:24px;margin:0!important}.application-trap{position:absolute!important;left:-10000px!important}.apply small{display:block;margin-top:14px;color:#707a90}@media(max-width:760px){.job-hero,.application-grid{grid-template-columns:1fr}.job-shell{padding-top:120px}}
    </style>
</head>
<body>
<a href="#main" class="btn skip-to-content">Skip to Content</a>
<div class="page" id="top">
@include('site.themes.crystal.partials.nav')
<main class="job-shell" id="main">
    <a class="job-back" href="/careers/">← All opportunities</a>
    <header class="job-hero"><div><h1>{{ $job->title }}</h1><p class="job-summary">{{ $job->summary }}</p></div><dl class="facts"><div><dt>Location</dt><dd>{{ $job->location }}</dd></div><div><dt>Job type</dt><dd>{{ $job->job_type }}</dd></div><div><dt>Hours</dt><dd>{{ $job->hours }}</dd></div><div><dt>Salary</dt><dd>{{ $job->salary }}</dd></div></dl></header>
    <article class="job-body">
        @if($job->description)<h2>The role</h2><p>{!! nl2br(e($job->description)) !!}</p>@endif
        @foreach(['responsibilities' => 'Key responsibilities', 'essential' => 'Essential skills and experience', 'desirable' => 'Desirable', 'benefits' => 'What we offer'] as $field => $heading)
            @if(count($job->{$field} ?? []))<h2>{{ $heading }}</h2><ul>@foreach($job->{$field} as $item)<li>{{ $item }}</li>@endforeach</ul>@endif
        @endforeach
        <section class="apply" id="apply"><h2>Apply for this role</h2><p>Complete the form and attach your CV. Your application will be stored securely and sent to the hiring team.</p>
            <form class="application-form" action="/api/v1/job-applications" method="post" enctype="multipart/form-data" data-job-application>
                <input type="hidden" name="job" value="{{ $job->slug }}">
                <label class="application-trap" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
                <div class="application-grid"><label>Full name<input name="name" required autocomplete="name"></label><label>Email address<input type="email" name="email" required autocomplete="email"></label></div>
                <label>Phone number <span class="visually-hidden">(optional)</span><input name="phone" autocomplete="tel"></label>
                <label>Cover letter<textarea name="cover_letter" rows="8" required></textarea></label>
                <label>CV <small>PDF, DOC or DOCX, up to 5 MB.</small><input type="file" name="cv" accept=".pdf,.doc,.docx" required></label>
                <button type="submit">Submit application</button>
                <p class="application-result" role="status" aria-live="polite"></p>
            </form>
            @if($job->closes_at)<small>Applications close {{ $job->closes_at->format('j F Y') }}</small>@endif
        </section>
    </article>
</main>
@include('site.themes.crystal.partials.footer')
</div>
<script src="/themes/crystal/js/jquery.min.js"></script>
<script src="/themes/crystal/js/bootstrap.bundle.min.js"></script>
<script src="/themes/crystal/js/plugins.js"></script>
<script src="/themes/crystal/js/all.js"></script>
<script>
document.querySelector('[data-job-application]')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const form = event.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    const result = form.querySelector('.application-result');
    button.disabled = true;
    result.textContent = 'Sending your application…';
    try {
        const response = await fetch(form.action, { method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(form) });
        const json = await response.json();
        if (!response.ok) throw new Error(Object.values(json.errors || {}).flat()[0] || json.message || 'Please check the form and try again.');
        form.reset();
        result.textContent = `${json.message} Reference: ${json.reference}.`;
    } catch (error) {
        result.textContent = error.message || 'Your application could not be sent. Please try again.';
    } finally {
        button.disabled = false;
    }
});
</script>
</body></html>
