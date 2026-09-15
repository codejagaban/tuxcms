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
                <li><a href="/">Home</a></li>
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
                        <span class="btn btn-mod btn-w btn-border-c btn-small btn-round" data-btn-animate="y">
                            Get in touch
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M5 4H9L11 9L8.5 10.5C9.57096 12.6715 11.3285 14.429 13.5 15.5L15 13L20 15V19C20 19.5304 19.7893 20.0391 19.4142 20.4142C19.0391 20.7893 18.5304 21 18 21C14.0993 20.763 10.4202 19.1065 7.65683 16.3432C4.8935 13.5798 3.23705 9.90074 3 6C3 5.46957 3.21071 4.96086 3.58579 4.58579C3.96086 4.21071 4.46957 4 5 4Z" stroke="#4567ED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
