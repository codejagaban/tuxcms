@php
    use App\Support\Site;
    $key = Site::web3formsKey();
    $options = data_get($d, 'services', []);
@endphp
@if ($key)
<section class="page-section" id="contact-form">
    <div class="container"><div class="row"><div class="col-lg-8 offset-lg-2">
        <div class="box-shadow round p-4 p-sm-5 bg-gradient-gray-light-1 bg-scroll">
            @if ($section->title)<h2 class="h3 mb-30 form-title">{{ $section->title }}</h2>@endif
            @if ($section->content)<p class="mb-30">{{ $section->content }}</p>@endif
            <form class="form contact-form" id="contact_form" onsubmit="handleFormSubmit(event)">
                <input type="hidden" name="access_key" value="{{ $key }}">
                <input type="hidden" name="subject" value="{{ data_get($d, 'subject', 'New website enquiry') }}">
                <input type="checkbox" name="botcheck" class="d-none" tabindex="-1" autocomplete="off">
                <div class="mb-3"><div id="success-message" class="d-none"></div><div id="error-message" class="d-none"></div></div>
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label for="name">Name</label><input type="text" name="name" id="name" class="input-lg round form-control" autocomplete="name" required></div></div>
                    <div class="col-md-6"><div class="form-group"><label for="email">Email</label><input type="email" name="email" id="email" class="input-lg round form-control" autocomplete="email" required></div></div>
                </div>
                <div class="form-group"><label for="phone">Phone number</label><input type="tel" name="Phone Number" id="phone" class="input-md round form-control" autocomplete="tel"></div>
                @if (count($options))
                    <div class="form-group"><label for="Service">Select a service</label><select name="Service" id="Service" class="input-md round form-control" required><option value="General Inquiry">General inquiry</option>@foreach($options as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select></div>
                @endif
                <div class="form-group"><label for="message">Message</label><textarea name="message" id="message" class="input-lg round form-control" style="height:130px" required></textarea></div>
                <div id="result" role="region" aria-live="polite" aria-atomic="true"></div>
                <div class="pt-3"><button class="submit_btn btn btn-mod btn-gray btn-large btn-round btn-hover-anim" id="submit_btn" type="submit"><span>{{ data_get($d, 'button_label', 'Send message') }}</span></button></div>
            </form>
        </div>
    </div></div></div>
</section>
@endif
