{{-- A section's head: its title, and its intro when it has one. Centred
     above what it introduces, or, beside a split, left in its column. --}}
@if (filled($section->title) || filled($section->content))
    <div @class(['row-head' => ! ($side ?? false), 'side-head' => $side ?? false])>
        @if (filled($section->title))
            <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
        @endif
        @if (filled($section->content))
            <p class="lede" @field($section, 'content')>{{ $section->content }}</p>
        @endif
    </div>
@endif
