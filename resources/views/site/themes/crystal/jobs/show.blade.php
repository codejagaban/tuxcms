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
    <link rel="stylesheet" href="/themes/crystal/css/demo-fancy/demo-fancy.css?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <style>
        .main-nav{opacity:1!important;background:#fff}
        body{background:#fff;color:#17213b}.job-shell{max-width:1120px;margin:auto;padding:145px 28px 100px}.job-back{display:inline-block;margin-bottom:42px;color:#536079}.job-hero{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:72px;padding-bottom:56px;border-bottom:1px solid #d9deea}.job-hero h1{font-size:clamp(44px,6vw,76px);line-height:1;letter-spacing:-.055em;margin:0 0 24px}.job-summary{font-size:20px;line-height:1.65;color:#536079;max-width:700px}.facts{margin:0}.facts div{padding:13px 0;border-bottom:1px solid #e4e7ef}.facts dt{font-size:12px;color:#707a90}.facts dd{margin:4px 0 0;font-weight:700}.job-body{max-width:760px;padding-top:58px}.job-body h2{font-size:29px;margin:48px 0 18px;letter-spacing:-.025em}.job-body p,.job-body li{font-size:17px;line-height:1.75;color:#3f4b63}.job-body ul{padding-left:22px}.apply{margin-top:58px;padding-top:38px;border-top:1px solid #d9deea}.apply a{display:inline-flex;padding:15px 22px;background:#17213b;color:#fff;font-weight:700;border-radius:4px}.apply small{display:block;margin-top:14px;color:#707a90}@media(max-width:760px){.job-hero{grid-template-columns:1fr;gap:34px}.job-shell{padding-top:120px}}
    </style>
</head>
<body>
@include('site.themes.crystal.partials.nav')
<main class="job-shell" id="main">
    <a class="job-back" href="/careers/">← All opportunities</a>
    <header class="job-hero"><div><h1>{{ $job->title }}</h1><p class="job-summary">{{ $job->summary }}</p></div><dl class="facts"><div><dt>Location</dt><dd>{{ $job->location }}</dd></div><div><dt>Job type</dt><dd>{{ $job->job_type }}</dd></div><div><dt>Hours</dt><dd>{{ $job->hours }}</dd></div><div><dt>Salary</dt><dd>{{ $job->salary }}</dd></div></dl></header>
    <article class="job-body">
        @if($job->description)<h2>The role</h2><p>{!! nl2br(e($job->description)) !!}</p>@endif
        @foreach(['responsibilities' => 'Key responsibilities', 'essential' => 'Essential skills and experience', 'desirable' => 'Desirable', 'benefits' => 'What we offer'] as $field => $heading)
            @if(count($job->{$field} ?? []))<h2>{{ $heading }}</h2><ul>@foreach($job->{$field} as $item)<li>{{ $item }}</li>@endforeach</ul>@endif
        @endforeach
        <section class="apply"><h2>How to apply</h2><p>Send your CV and a covering letter outlining your relevant experience and suitability for the role.</p><a href="mailto:{{ $job->application_email ?: \App\Models\Setting::getString('contact_email') }}?subject={{ rawurlencode('Application: '.$job->title) }}">Apply by email</a>@if($job->closes_at)<small>Applications close {{ $job->closes_at->format('j F Y') }}</small>@endif</section>
    </article>
</main>
@include('site.themes.crystal.partials.footer')
</body></html>
