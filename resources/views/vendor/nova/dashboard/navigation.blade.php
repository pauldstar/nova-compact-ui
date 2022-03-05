<router-link
    exact tag="li"
    :to="{ name: 'dashboard.custom', params: { name: 'main' } }"
>
    <a class="text-xs font-bold" aria-current="page">
        <i class="material-icons">dashboard</i>
        <p>Dashboard</p>
    </a>
</router-link>

@if (\Laravel\Nova\Nova::availableDashboards(request()))
    <ul class="list-reset mb-8">
        @foreach (\Laravel\Nova\Nova::availableDashboards(request()) as $dashboard)
            <li class="leading-wide mb-4">
                <router-link :to='{
                    name: "dashboard.custom",
                    params: {
                    name: "{{ $dashboard::uriKey() }}",
                    },
                    query: @json($dashboard->meta()),
                    }'
                             exact
                             class="text-white ml-8 no-underline dim">
                    {{ $dashboard::label() }}
                </router-link>
            </li>
        @endforeach
    </ul>
@endif
