@php
    // This list lives under `members`, not `items`.
    $members = data_get($d, 'members', []);
@endphp
@if (count($members))
{{-- The career cards of the Time Tracking template, one to a person: the
     role as a tag, the name, a few words. --}}
<section class="block" @tuxsection($section)>
    <div class="wrap">
        @include('site.themes.shopfront.partials.head')
        <ul class="posts posts--three">
            @foreach ($members as $i => $member)
                <li class="post">
                    @if (filled(data_get($member, 'role')))
                        <p class="post__tags"><span class="post__tag" @field($section, "data.members.{$i}.role")>{{ data_get($member, 'role') }}</span></p>
                    @endif
                    <h3 class="post__name" @field($section, "data.members.{$i}.name")>{{ data_get($member, 'name') }}</h3>
                    @if (filled(data_get($member, 'bio')))
                        <p class="post__what" @field($section, "data.members.{$i}.bio")>{{ data_get($member, 'bio') }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
