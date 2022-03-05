<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full font-sans antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1280">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ \Laravel\Nova\Nova::name() }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,800,800i,900,900i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('app.css', 'vendor/nova') }}">

    <style>
        .nav-group {
            color: #64ace4;
        }
        .w-sidebar {
            width: 16.75rem;
        }
        #content {
            padding-bottom: 3.125rem;
        }
        .content {
            max-width: calc(100vw - 80px);
        }
        .table th {
            padding: .25rem .75rem;
        }
        .table td {
            padding: .45rem .75rem;
            height: unset;
        }
    </style>

    <!-- Tool Styles -->
    @foreach(\Laravel\Nova\Nova::availableStyles(request()) as $name => $path)
        <link rel="stylesheet" href="/nova-api/styles/{{ $name }}">
    @endforeach

<!-- Custom Meta Data -->
    @include('nova::partials.meta')

<!-- Theme Styles -->
    @foreach(\Laravel\Nova\Nova::themeStyles() as $publicPath)
        <link rel="stylesheet" href="{{ $publicPath }}">
    @endforeach
</head>
<body class="min-w-site bg-40 text-black min-h-full">
<div id="nova">
    <div v-cloak class="flex flex-row-reverse min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar min-h-screen flex-none bg-grad-sidebar">
            <div class="logo h-header">
                @include('nova::partials.logo')
            </div>

            <div class="sidebar-wrapper">
                <ul class="nav">
                    @foreach (\Laravel\Nova\Nova::availableTools(request()) as $tool)
                        {!! $tool->renderNavigation() !!}
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="flex items-center relative shadow h-header bg-white z-20 px-view">
                @if (count(\Laravel\Nova\Nova::globallySearchableResources(request())) > 0)
                    <global-search dusk="global-search-component"></global-search>
                @endif

                <dropdown class="ml-auto h-9 flex items-center dropdown-right">
                    @include('nova::partials.user')
                </dropdown>
            </div>

            <div id="content" data-testid="content" class="px-view mx-auto">
                @yield('content')

                @include('nova::partials.footer')
            </div>
        </div>
    </div>
</div>

<script>
    window.config = @json(\Laravel\Nova\Nova::jsonVariables(request()));
</script>

<!-- Scripts -->
<script src="{{ mix('manifest.js', 'vendor/nova') }}"></script>
<script src="{{ mix('vendor.js', 'vendor/nova') }}"></script>
<script src="{{ mix('app.js', 'vendor/nova') }}"></script>

<!-- Build Nova Instance -->
<script>
    window.Nova = new CreateNova(config)
</script>

<!-- Tool Scripts -->
@foreach (\Laravel\Nova\Nova::availableScripts(request()) as $name => $path)
    @if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://']))
        <script src="{!! $path !!}"></script>
    @else
        <script src="/nova-api/scripts/{{ $name }}"></script>
    @endif
@endforeach

<!-- Start Nova -->
<script>
    Nova.liftOff()
</script>

<script>
    let i, collapsible = document.getElementsByClassName('collapsible');

    each(collapsible, function(coll) {
        coll.addEventListener('click', function() {
            let wasOpen = this.classList.contains('show');
            each(collapsible, coll2 => closeCollapsible(coll2))
            wasOpen || openCollapsible(this);
        });
    });

    function closeCollapsible(el)
    {
        if (el.classList.contains('show'))
        {
            el.classList.remove('show');
            el.nextElementSibling.style.maxHeight = null;
        }
    }

    function openCollapsible(el)
    {
        el.classList.add('show');
        el.nextElementSibling.style.maxHeight = el.nextElementSibling.scrollHeight + 'px';
    }

    function each(list, fn)
    {
        for (i = 0; i < list.length; i++) {
            fn(list[i]);
        }
    }
</script>
</body>
</html>
