@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
{{-- The figures of "The team behind smarter automation" in the Automation
     SaaS template: the heading and its words on one side, and the figures in
     a plain grid on the other, each a large regular-weight number over a
     quiet label. --}}
<section class="block" @tuxsection($section)>
    <div class="wrap figures">
        @include('site.themes.shopfront.partials.head', ['side' => true])
        <dl class="figures__list">
            @foreach ($items as $i => $item)
                <div class="figures__item">
                    <dt class="figures__of" @field($section, "data.items.{$i}.label")>{{ data_get($item, 'label') }}</dt>
                    <dd class="figures__n tally__n" @field($section, "data.items.{$i}.value")>{{ data_get($item, 'value') }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
@endif
