<section @tuxsection($section)>
    <h2 @field($section, 'title')>{{ $section->title }}</h2>
    @if (filled($d['intro'] ?? null))
        <p @field($section, 'data.intro')>{{ $d['intro'] }}</p>
    @endif
    @foreach ($d['items'] ?? [] as $i => $item)
        <article>
            <img src="{{ $item['image'] ?? '' }}" alt="" @image($section, "data.items.{$i}.image")>
            <h3 @field($section, "data.items.{$i}.name")>{{ $item['name'] ?? '' }}</h3>
            <p @field($section, "data.items.{$i}.price")>{{ $item['price'] ?? '' }}</p>
            <ul>
                @foreach ($item['features'] ?? [] as $f => $feature)
                    <li @field($section, "data.items.{$i}.features.{$f}")>{{ $feature }}</li>
                @endforeach
            </ul>
            @if (filled($item['url'] ?? null))
                <a href="{{ $item['url'] }}">Book</a>
            @endif
        </article>
    @endforeach
</section>
