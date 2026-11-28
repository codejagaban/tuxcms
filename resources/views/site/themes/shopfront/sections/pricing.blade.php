@php
    $plans = data_get($d, 'plans', []);
    $promises = array_filter(data_get($d, 'promises', []), 'filled');
    $tick = '<svg class="plan__tick" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M4.6 8.3l2.3 2.3 4.5-4.8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp
@if (count($plans))
{{-- The pricing block of the Automation SaaS template: the plans to pick
     between on the left in a tinted tray, the chosen one lit white, and its
     card on the right with its price, a button and what it includes. Each
     plan in the tray links to its card, so with no script, and in the
     dashboard's preview, every card shows, one under another. --}}
<section class="block" @tuxsection($section)>
    <div class="wrap">
        @include('site.themes.shopfront.partials.head')
        <div class="plans" data-plans>
            <div class="plans__pick">
                <div class="plans__tray">
                    @foreach ($plans as $i => $plan)
                        <a class="plans__tab" href="#plan-{{ $section->id }}-{{ $i }}" data-plan-tab="{{ $i }}" @if ($i === 0) aria-current="true" @endif>
                            <span class="plans__words">
                                <span class="plans__name" @field($section, "data.plans.{$i}.name")>{{ data_get($plan, 'name') }}</span>
                                @if (filled(data_get($plan, 'summary')))
                                    <span class="plans__for" @field($section, "data.plans.{$i}.summary")>{{ data_get($plan, 'summary') }}</span>
                                @endif
                            </span>
                            <span class="plans__go" aria-hidden="true">@include('site.themes.shopfront.partials.arrow')</span>
                        </a>
                    @endforeach
                </div>
                @if (count($promises))
                    <ul class="plans__promises">
                        @foreach ($promises as $p => $promise)
                            <li>{!! $tick !!}<span @field($section, "data.promises.{$p}")>{{ $promise }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="plans__cards">
                @foreach ($plans as $i => $plan)
                    @php $features = array_filter(data_get($plan, 'features', []), 'filled'); @endphp
                    <article class="plan" id="plan-{{ $section->id }}-{{ $i }}" data-plan="{{ $i }}">
                        <div class="plan__top">
                            @if (filled(data_get($plan, 'summary')))
                                <span class="plan__badge" @field($section, "data.plans.{$i}.summary")>{{ data_get($plan, 'summary') }}</span>
                            @endif
                            <div class="plan__row">
                                <h3 class="plan__title" @field($section, "data.plans.{$i}.name")>{{ data_get($plan, 'name') }}</h3>
                                <p class="plan__price">
                                    <span @field($section, "data.plans.{$i}.price")>{{ data_get($plan, 'price') }}</span>
                                    @if (filled(data_get($plan, 'period')))
                                        <span class="plan__per" @field($section, "data.plans.{$i}.period")>{{ data_get($plan, 'period') }}</span>
                                    @endif
                                </p>
                            </div>
                            @if (filled(data_get($plan, 'description')))
                                <p class="plan__what" @field($section, "data.plans.{$i}.description")>{{ data_get($plan, 'description') }}</p>
                            @endif
                            @if (filled(data_get($plan, 'cta_label')) && filled(data_get($plan, 'url')))
                                <a class="button plan__button" href="{{ data_get($plan, 'url') }}"><span @field($section, "data.plans.{$i}.cta_label")>{{ data_get($plan, 'cta_label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                            @endif
                        </div>
                        @if (count($features))
                            <div class="plan__in">
                                <h4 class="plan__in-head">What is included</h4>
                                <ul class="plan__list">
                                    @foreach ($features as $f => $feature)
                                        <li><span class="plan__ring">{!! $tick !!}</span><span @field($section, "data.plans.{$i}.features.{$f}")>{{ $feature }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
