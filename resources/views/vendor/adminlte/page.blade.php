@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
    <link rel="stylesheet" href="{{ asset('css/mobile-dock.css') }}">
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation --}}
        @if($layoutHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

        {{-- Mobile Dock Menu --}}
        <nav class="mobile-dock">
            <ul class="dock-menu">
                <li>
                    <a href="/home" class="dock-item" data-route="home">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="/booking" class="dock-item" data-route="booking">
                        <i class="fas fa-book"></i>
                        <span>Booking</span>
                    </a>
                </li>
                <li>
                    <a href="/ralan/pasien" class="dock-item" data-route="ralan">
                        <i class="fas fa-stethoscope"></i>
                        <span>Ralan</span>
                    </a>
                </li>
                <li>
                    <a href="/ranap/pasien" class="dock-item" data-route="ranap">
                        <i class="fas fa-bed"></i>
                        <span>Ranap</span>
                    </a>
                </li>
                <li>
                    <a href="/igd" class="dock-item" data-route="igd">
                        <i class="fas fa-stethoscope"></i>
                        <span>IGD</span>
                    </a>
                </li>
                <li>
                    <a href="/konsultasi" class="dock-item" data-route="konsultasi">
                        <i class="fas fa-user"></i>
                        <span>Konsultasi</span>
                    </a>
                </li>
                <li>
                    <a href="/rekap/tindakan-dokter" class="dock-item" data-route="rekap">
                        <i class="fas fa-chart-line"></i>
                        <span>Rekap</span>
                    </a>
                </li>
            </ul>
        </nav>

    </div>

@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
    <script>
        // Set active menu item in mobile dock based on current URL
        (function() {
            var currentPath = window.location.pathname;
            var dockItems = document.querySelectorAll('.dock-item');
            
            dockItems.forEach(function(item) {
                var itemHref = item.getAttribute('href');
                // Remove leading slash for comparison
                var cleanCurrentPath = currentPath.replace(/\/$/, '');
                var cleanItemHref = itemHref.replace(/\/$/, '');
                
                // Check if current path matches or starts with item href
                if (cleanCurrentPath === cleanItemHref || 
                    (cleanItemHref !== '' && cleanCurrentPath.startsWith(cleanItemHref + '/'))) {
                    item.classList.add('active');
                }
            });
        })();
    </script>
@stop
