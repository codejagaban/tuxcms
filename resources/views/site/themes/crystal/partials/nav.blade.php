@php use App\Support\Site; @endphp
<nav class="main-nav transparent stick-fixed wow-menubar wch-unset" aria-label="Main navigation">
    <div class="main-nav-sub container">
        <div class="nav-logo-wrap position-static local-scroll">
            <a href="/" class="logo">
                <img src="{{ Site::logo() ?: '/themes/crystal/images/logo/logo.svg' }}" alt="{{ Site::name() }}" width="159" height="48">
            </a>
        </div>

        <div class="mobile-nav">
            <div class="flex items-center gap-20">
                <div role="button" tabindex="0" aria-label="Open navigation menu">
                    <i class="mobile-nav-icon"></i>
                    <span class="visually-hidden">Menu</span>
                </div>
            </div>
        </div>

        <div class="inner-nav desktop-nav">
            <ul class="clearlist local-scroll justify-content-end">
                @foreach ($navigation as $item)
                    <li>
                        <a href="{{ $item['url'] }}" @class(['active' => $item['active']])>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
                <li class="desktop-nav-display"><div class="vr mt-2"></div></li>
                <li>
                    <a href="/contact/" class="opacity-1 no-hover">
                        <span class="btn btn-mod btn-w btn-border-c btn-small btn-round">Get in touch</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
