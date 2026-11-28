@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="block" @tuxsection($section)>
    <div class="wrap split">
        <div>
            @include('site.themes.shopfront.partials.head', ['side' => true])
        </div>
        {{-- Native <details> keeps every answer in the page for crawlers with
             no JavaScript, which is what makes the FAQPage schema honest. --}}
        <div class="asks">
            @foreach ($items as $i => $item)
                <details class="ask">
                    <summary>
                        <span @field($section, "data.items.{$i}.question")>{{ data_get($item, 'question') }}</span>
                        <span class="ask__mark" aria-hidden="true"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" focusable="false"><path d="M6 2v8M2 6h8"/></svg></span>
                    </summary>
                    <div class="ask__answer" @field($section, "data.items.{$i}.answer")>{{ data_get($item, 'answer') }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif
