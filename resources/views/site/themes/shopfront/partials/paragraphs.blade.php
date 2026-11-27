{{-- One element per paragraph. The editor makes each paragraph editable on
     its own, so a blank line in the text must become a new element. --}}
@foreach (preg_split('/\n\s*\n/', trim((string) $text), -1, PREG_SPLIT_NO_EMPTY) as $paragraph)
    <p @class([$class ?? null])>{{ trim($paragraph) }}</p>
@endforeach
