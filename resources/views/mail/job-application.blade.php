<h1>New application for {{ $application->jobPost->title }}</h1>
<p><strong>Name:</strong> {{ $application->name }}</p>
<p><strong>Email:</strong> {{ $application->email }}</p>
@if($application->phone)<p><strong>Phone:</strong> {{ $application->phone }}</p>@endif
<h2>Cover letter</h2>
<p>{!! nl2br(e($application->cover_letter)) !!}</p>
<p>The applicant's CV is attached to this email.</p>
