@php
    // The name set as the dashboard sets its own: the first part heavy, tight
    // and in capitals, and a trailing run of capitals (TuxCMS's "CMS") small
    // beside it. A name with no such run is set whole.
    $wordmark = \App\Support\Site::name();
    preg_match('/^(.+?)\s*([A-Z]{2,})$/u', $wordmark, $wordmarkParts);
@endphp
<span class="bar__mark" aria-hidden="true">{{ $wordmarkParts[1] ?? $wordmark }}</span>
@if (! empty($wordmarkParts[2]))
    <span class="bar__tail" aria-hidden="true">{{ $wordmarkParts[2] }}</span>
@endif
