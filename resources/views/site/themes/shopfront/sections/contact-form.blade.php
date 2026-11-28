@php
    use App\Support\Site;

    // With a Web3Forms key, on the section or site-wide, the form goes there.
    // Without one it goes to the CMS's own inbox, where it shows up under
    // Enquiries in the dashboard and is emailed on from there.
    $accessKey = data_get($d, 'access_key') ?: Site::web3formsKey();
    $ownInbox = blank($accessKey);
    $fields = data_get($d, 'fields', []);
    $formId = 'form-' . $section->id;

    // The inbox has columns for these; anything else travels as a detail.
    $columns = ['name' => 'name', 'email' => 'email', 'phone' => 'phone', 'message' => 'message', 'organisation' => 'organisation', 'company' => 'organisation'];
    $privacy = collect(Site::legalLinks())->firstWhere('label', 'Privacy');
@endphp
@if (count($fields))
<section class="block" @tuxsection($section)>
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
        </div>

        <form id="{{ $formId }}" class="form" method="POST"
              action="{{ $ownInbox ? '/api/v1/enquiries' : 'https://api.web3forms.com/submit' }}"
              data-inbox="{{ $ownInbox ? 'own' : 'web3forms' }}">
            @if ($ownInbox)
                <input type="hidden" name="type" value="contact">
                <input type="hidden" name="form_token" value="">
                {{-- Honeypot: people never see it, bots fill it in. --}}
                <label class="form__trap" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
            @else
                <input type="hidden" name="access_key" value="{{ $accessKey }}">
                <input type="hidden" name="subject" value="{{ data_get($d, 'subject', $page->title . ' enquiry') }}">
                <input type="hidden" name="from_name" value="{{ Site::name() }}">
                <input type="checkbox" name="botcheck" class="form__trap" tabindex="-1" autocomplete="off" aria-hidden="true">
            @endif

            @foreach ($fields as $i => $field)
                @php
                    $key = data_get($field, 'name', 'field_' . $i);
                    $id = $formId . '-' . $key;
                    $type = data_get($field, 'type', 'text');
                    $required = (bool) data_get($field, 'required');
                    $name = $ownInbox ? ($columns[$key] ?? 'details[' . $key . ']') : $key;
                @endphp
                <div class="field">
                    <label for="{{ $id }}">{{ data_get($field, 'label', $key) }}@unless ($required) <small>(optional)</small>@endunless</label>

                    @if ($type === 'textarea')
                        <textarea id="{{ $id }}" name="{{ $name }}" rows="5" @required($required) placeholder="{{ data_get($field, 'placeholder') }}"></textarea>
                    @elseif ($type === 'select')
                        <select id="{{ $id }}" name="{{ $name }}" @required($required)>
                            <option value="">Please choose</option>
                            @foreach (data_get($field, 'options', []) as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @required($required) placeholder="{{ data_get($field, 'placeholder') }}">
                    @endif
                </div>
            @endforeach

            @if ($ownInbox)
                <label class="consent">
                    <input type="checkbox" name="consent" value="1" required>
                    <span>I agree to {{ Site::name() }} using these details to reply to me.@if ($privacy) <a href="{{ $privacy['url'] }}">Privacy</a>@endif</span>
                </label>
            @endif

            <div>
                <button type="submit" class="button">@include('site.themes.shopfront.partials.chip')<span @field($section, 'data.button_label')>{{ data_get($d, 'button_label', 'Send message') }}</span></button>
            </div>
            <p class="form__result" role="status" aria-live="polite" data-result></p>
        </form>

        {{-- Sent over fetch so the visitor stays on the page. --}}
        <script>
            (function () {
                var form = document.getElementById(@json($formId));
                if (!form) return;
                var own = form.dataset.inbox === 'own';
                var result = form.querySelector('[data-result]');
                var success = @json(data_get($d, 'success_message'));

                // Proof the form page was opened, and when. The inbox holds
                // back anything sent without it.
                if (own) {
                    fetch('/api/v1/form-token', { headers: { Accept: 'application/json' } })
                        .then(function (response) { return response.json(); })
                        .then(function (json) { form.elements.form_token.value = json.token || ''; })
                        .catch(function () {});
                }

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    var button = form.querySelector('button[type=submit]');
                    button.disabled = true;
                    result.textContent = 'Sending';

                    fetch(form.action, { method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(form) })
                        .then(function (response) {
                            return response.json().then(function (json) {
                                var ok = own ? response.ok : json.success;
                                if (!ok) {
                                    var first = Object.values(json.errors || {}).flat()[0];
                                    throw new Error(first || json.message || 'Something went wrong. Please try again.');
                                }
                                form.reset();
                                result.textContent = success || json.message || 'Thanks. Your message has been sent.';
                            });
                        })
                        .catch(function (error) { result.textContent = error.message || 'Network error. Please try again.'; })
                        .finally(function () { button.disabled = false; });
                });
            })();
        </script>
    </div>
</section>
@endif
