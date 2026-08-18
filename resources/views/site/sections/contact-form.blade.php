@php
    use App\Support\Site;

    // A per-section key overrides the site-wide one.
    $accessKey = data_get($d, 'access_key') ?: Site::web3formsKey();
    $fields = data_get($d, 'fields', []);
    $formId = 'form-' . $section->id;
@endphp
<section class="mx-auto max-w-xl px-6 py-14">
    @if (filled($section->title))
        <h2 class="mb-6 text-2xl font-bold text-gray-900">{{ $section->title }}</h2>
    @endif

    @if (blank($accessKey))
        {{-- Better an honest notice than a form that silently discards enquiries. --}}
        <p class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
            This form isn’t connected yet. Add a Web3Forms access key in Settings.
        </p>
    @elseif (count($fields))
        <form id="{{ $formId }}" action="https://api.web3forms.com/submit" method="POST"
              class="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
            <input type="hidden" name="access_key" value="{{ $accessKey }}">
            <input type="hidden" name="subject" value="{{ data_get($d, 'subject', $page->title . ' enquiry') }}">
            <input type="hidden" name="from_name" value="{{ Site::name() }}">

            {{-- Honeypot: hidden from people, tempting to bots. --}}
            <input type="checkbox" name="botcheck" class="hidden" style="display:none"
                   tabindex="-1" autocomplete="off" aria-hidden="true">

            @foreach ($fields as $i => $field)
                @php
                    $name = data_get($field, 'name', 'field_' . $i);
                    $id = $formId . '-' . $name;
                    $type = data_get($field, 'type', 'text');
                    $required = (bool) data_get($field, 'required');
                    $options = data_get($field, 'options', []);
                @endphp
                <div>
                    <label for="{{ $id }}" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ data_get($field, 'label', $name) }}@if ($required)<span aria-hidden="true"> *</span>@endif
                    </label>

                    @if ($type === 'textarea')
                        <textarea id="{{ $id }}" name="{{ $name }}" rows="4" @required($required)
                            placeholder="{{ data_get($field, 'placeholder') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    @elseif ($type === 'select')
                        <select id="{{ $id }}" name="{{ $name }}" @required($required)
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Please choose…</option>
                            @foreach ($options as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @required($required)
                            placeholder="{{ data_get($field, 'placeholder') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @endif
                </div>
            @endforeach

            <button type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                {{ data_get($d, 'button_label', 'Send message') }}
            </button>

            <p role="status" aria-live="polite" class="text-sm text-gray-600" data-result></p>
        </form>

        {{-- Submit over fetch so the visitor stays on the page. Without JS the
             form still posts normally to Web3Forms, so it never breaks. --}}
        <script>
            (function () {
                var form = document.getElementById(@json($formId));
                if (!form) return;
                var result = form.querySelector('[data-result]');
                var success = @json(data_get($d, 'success_message', 'Thanks — your message has been sent.'));

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    var button = form.querySelector('button[type=submit]');
                    button.disabled = true;
                    result.textContent = 'Sending…';

                    fetch(form.action, {
                        method: 'POST',
                        headers: { Accept: 'application/json' },
                        body: new FormData(form)
                    })
                        .then(function (response) { return response.json(); })
                        .then(function (json) {
                            if (json.success) {
                                form.reset();
                                result.textContent = success;
                            } else {
                                result.textContent = json.message || 'Something went wrong. Please try again.';
                            }
                        })
                        .catch(function () {
                            result.textContent = 'Network error. Please try again.';
                        })
                        .finally(function () { button.disabled = false; });
                });
            })();
        </script>
    @endif
</section>
