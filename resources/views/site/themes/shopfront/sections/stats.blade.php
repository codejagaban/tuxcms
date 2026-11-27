@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="block">
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head">{{ $section->title }}</h2>
            @endif
        </div>
        <dl class="tally">
            @foreach ($items as $item)
                {{-- The label is the term and the figure its value, but the
                     figure is painted first. --}}
                <div class="tally__item">
                    <dt class="tally__of">{{ data_get($item, 'label') }}</dt>
                    <dd class="tally__n">{{ data_get($item, 'value') }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
@endif
