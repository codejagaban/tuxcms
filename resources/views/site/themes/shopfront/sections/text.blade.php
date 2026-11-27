<section class="block">
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head">{{ $section->title }}</h2>
            @endif
        </div>
        <div class="prose">
            @include('site.themes.shopfront.partials.paragraphs', ['text' => $section->content])
        </div>
    </div>
</section>
