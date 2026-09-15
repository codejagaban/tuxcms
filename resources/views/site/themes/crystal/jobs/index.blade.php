<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Careers — Crystal Services Limited</title>
    <meta name="description" content="Current opportunities at Crystal Services Limited in Worcester.">
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
        body{background:#fff;color:#17213b}.career-shell{max-width:1120px;margin:auto;padding:150px 28px 96px}.career-head{max-width:760px;margin-bottom:64px}.career-head h1{font-size:clamp(48px,7vw,82px);line-height:.98;letter-spacing:-.055em;margin:0 0 24px}.career-head p{font-size:20px;line-height:1.65;color:#536079}.job-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:32px;padding:34px 0;border-top:1px solid #d9deea;color:inherit}.job-row:last-child{border-bottom:1px solid #d9deea}.job-row h2{font-size:29px;letter-spacing:-.025em;margin:0 0 14px}.job-meta{display:flex;flex-wrap:wrap;gap:8px 22px;color:#657089;font-size:15px}.job-action{align-self:center;font-weight:700;color:#2458dc}.empty{padding:32px 0;border-top:1px solid #d9deea;border-bottom:1px solid #d9deea;color:#657089}@media(max-width:700px){.career-shell{padding-top:120px}.job-row{grid-template-columns:1fr}.job-action{justify-self:start}}
    </style>
</head>
<body>
<a href="#main" class="btn skip-to-content">Skip to Content</a>
<div class="page" id="top">
@include('site.themes.crystal.partials.nav')
<main class="career-shell" id="main">
    <header class="career-head"><h1>Work with Crystal</h1><p>Join a growing Worcester business delivering professional cleaning and hairdressing services.</p></header>
    @forelse($jobs as $job)
        <a class="job-row" href="/careers/{{ $job->slug }}/"><div><h2>{{ $job->title }}</h2><div class="job-meta"><span>{{ $job->location }}</span><span>{{ $job->job_type }}</span><span>{{ $job->salary }}</span></div></div><span class="job-action">View role ↗</span></a>
    @empty <p class="empty">There are no open roles at the moment.</p> @endforelse
</main>
@include('site.themes.crystal.partials.footer')
</div>
</body></html>
