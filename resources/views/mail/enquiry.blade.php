<h1>{{ $enquiry->typeLabel() }}</h1>
<p><strong>Name:</strong> {{ $enquiry->name }}</p>
@if($enquiry->organisation)<p><strong>Organisation:</strong> {{ $enquiry->organisation }}</p>@endif
<p><strong>Email:</strong> {{ $enquiry->email }}</p>
@if($enquiry->phone)<p><strong>Phone:</strong> {{ $enquiry->phone }}</p>@endif
@foreach(($enquiry->details ?? []) as $label => $value)
    <p><strong>{{ ucfirst(str_replace('_', ' ', $label)) }}:</strong> {{ is_array($value) ? implode(', ', $value) : $value }}</p>
@endforeach
@if($enquiry->message)
    <h2>Message</h2>
    <p>{!! nl2br(e($enquiry->message)) !!}</p>
@endif
