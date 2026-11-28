@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
{{-- The figures stand on a band of the editor's blue. --}}
<section class="block tally" @tuxsection($section)>
    <div class="wrap">
        @if (filled($section->title))
            <h2 class="display head tally__head" @field($section, 'title')>{{ $section->title }}</h2>
        @endif
        <dl class="tally__list tally__list--{{ min(4, count($items)) }}">
            @foreach ($items as $i => $item)
                {{-- The label is the term and the figure its value, but the
                     figure is shown first. --}}
                <div class="tally__item">
                    <dt class="tally__of" @field($section, "data.items.{$i}.label")>{{ data_get($item, 'label') }}</dt>
                    <dd class="tally__n" @field($section, "data.items.{$i}.value")>{{ data_get($item, 'value') }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
@endif
