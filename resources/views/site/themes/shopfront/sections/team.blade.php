@php
    // This list lives under `members`, not `items`.
    $members = data_get($d, 'members', []);
@endphp
@if (count($members))
<section class="block" @tuxsection($section)>
    <div class="wrap">
        @include('site.themes.shopfront.partials.head')
        <ul class="staff">
            @foreach ($members as $i => $member)
                <li class="person">
                    <h3 class="person__name" @field($section, "data.members.{$i}.name")>{{ data_get($member, 'name') }}</h3>
                    @if (filled(data_get($member, 'role')))
                        <p class="person__role" @field($section, "data.members.{$i}.role")>{{ data_get($member, 'role') }}</p>
                    @endif
                    @if (filled(data_get($member, 'bio')))
                        <p class="person__bio" @field($section, "data.members.{$i}.bio")>{{ data_get($member, 'bio') }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
