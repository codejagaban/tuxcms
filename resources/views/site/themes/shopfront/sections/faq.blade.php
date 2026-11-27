@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="block">
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head">{{ $section->title }}</h2>
            @endif
        </div>
        {{-- Native <details> keeps every answer in the page for crawlers with
             no JavaScript, which is what makes the FAQPage schema honest. --}}
        <div class="asks">
            @foreach ($items as $item)
                <details class="ask">
                    <summary>
                        <span>{{ data_get($item, 'question') }}</span>
                        <svg class="ask__mark" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="square" aria-hidden="true" focusable="false"><path d="M6 1.5v9M1.5 6h9"/></svg>
                    </summary>
                    <div class="ask__answer">{{ data_get($item, 'answer') }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif
