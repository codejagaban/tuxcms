@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
{{-- The testimonial slider of the Automation SaaS template: the heading on
     the left with the arrows and a count beneath it, and the words on the
     right in a white card, one at a time. With no script every card shows,
     one under another. --}}
<section class="block" @tuxsection($section)>
    <div class="wrap voices" data-voices>
        <div class="voices__side">
            @include('site.themes.shopfront.partials.head', ['side' => true])
            @if (count($items) > 1)
                <div class="voices__nav">
                    <button type="button" class="voices__arrow" data-voices-prev aria-label="Previous">
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M10 3.5 5.5 8l4.5 4.5"/></svg>
                    </button>
                    <button type="button" class="voices__arrow" data-voices-next aria-label="Next">
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 3.5 10.5 8 6 12.5"/></svg>
                    </button>
                    <p class="voices__count" aria-live="polite"><span data-voices-at>1</span><span class="voices__of">/{{ count($items) }}</span></p>
                </div>
            @endif
        </div>
        <div class="voices__track">
            @foreach ($items as $i => $item)
                <figure class="voice" data-voice>
                    <blockquote @field($section, "data.items.{$i}.quote")>{{ data_get($item, 'quote') }}</blockquote>
                    <figcaption>
                        <span class="voice__who" @field($section, "data.items.{$i}.author")>{{ data_get($item, 'author') }}</span>
                        @if (filled(data_get($item, 'role')))
                            <span class="voice__role" @field($section, "data.items.{$i}.role")>{{ data_get($item, 'role') }}</span>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif
