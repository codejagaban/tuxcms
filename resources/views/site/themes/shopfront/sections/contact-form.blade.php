@php
    use App\Support\Site;

    // A per-section key overrides the site-wide one.
    $accessKey = data_get($d, 'access_key') ?: Site::web3formsKey();
    $fields = data_get($d, 'fields', []);
    $formId = 'form-' . $section->id;
@endphp
<section class="block">
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head">{{ $section->title }}</h2>
            @endif
        </div>

        @if (blank($accessKey))
            {{-- Better an honest notice than a form that silently discards enquiries. --}}
            <p class="notice">This form is not connected yet. Add a Web3Forms access key in Settings.</p>
        @elseif (count($fields))
            <form id="{{ $formId }}" class="form" action="https://api.web3forms.com/submit" method="POST">
                <input type="hidden" name="access_key" value="{{ $accessKey }}">
                <input type="hidden" name="subject" value="{{ data_get($d, 'subject', $page->title . ' enquiry') }}">
                <input type="hidden" name="from_name" value="{{ Site::name() }}">

                {{-- Honeypot: hidden from people, tempting to bots. --}}
                <input type="checkbox" name="botcheck" class="form__trap" tabindex="-1" autocomplete="off" aria-hidden="true">

                @foreach ($fields as $i => $field)
                    @php
                        $name = data_get($field, 'name', 'field_' . $i);
                        $id = $formId . '-' . $name;
                        $type = data_get($field, 'type', 'text');
                        $required = (bool) data_get($field, 'required');
                    @endphp
                    <div class="field">
                        <label for="{{ $id }}">{{ data_get($field, 'label', $name) }}@unless ($required) <small>(optional)</small>@endunless</label>

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

                <div>
                    <button type="submit" class="plate"><span>{{ data_get($d, 'button_label', 'Send message') }}</span>@include('site.themes.shopfront.partials.arrow')</button>
                </div>
                <p class="form__result" role="status" aria-live="polite" data-result></p>
            </form>

            {{-- Sent over fetch so the visitor stays on the page. Without
                 JavaScript the form still posts normally to Web3Forms. --}}
            <script>
                (function () {
                    var form = document.getElementById(@json($formId));
                    if (!form) return;
                    var result = form.querySelector('[data-result]');
                    var success = @json(data_get($d, 'success_message', 'Thanks. Your message has been sent.'));

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        var button = form.querySelector('button[type=submit]');
                        button.disabled = true;
                        result.textContent = 'Sending';

                        fetch(form.action, { method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(form) })
                            .then(function (response) { return response.json(); })
                            .then(function (json) {
                                if (json.success) {
                                    form.reset();
                                    result.textContent = success;
                                } else {
                                    result.textContent = json.message || 'Something went wrong. Please try again.';
                                }
                            })
                            .catch(function () { result.textContent = 'Network error. Please try again.'; })
                            .finally(function () { button.disabled = false; });
                    });
                })();
            </script>
        @endif
    </div>
</section>
