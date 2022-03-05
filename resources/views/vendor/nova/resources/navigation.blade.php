@if (count(\Laravel\Nova\Nova::availableResources(request())))
    @foreach($navigation as $group => $resources)
        @if (count($groups) > 1)
            <li>
                <a type="button" data-toggle="collapse" class="collapsible text-xs font-bold">
                    <i class="material-icons">{{ \App\Nova\Resource::groupIcon($group) }}</i>
                    <p>{{ $group }}<b class="caret"></b></p>
                </a>
                <div class="nav-dropdown">
                    <ul class="nav">
                        @foreach($resources as $resource)
                            <router-link
                                exact tag="li"
                                :to="{ name: 'index', params: { resourceName: '{{ $resource::uriKey() }}' } }"
                            >
                                <a class="text-xs font-semibold" aria-current="page">
                                    <span class="sidebar-mini-icon">{{ $resource::labelAbbr() }}</span>
                                    <span class="sidebar-normal">{{ $resource::label() }}</span>
                                </a>
                            </router-link>
                        @endforeach
                    </ul>
                </div>
            </li>
        @else
            @foreach($resources as $resource)
                <router-link
                    exact tag="li"
                    :to="{ name: 'index', params: { resourceName: '{{ $resource::uriKey() }}' } }"
                >
                    <a aria-current="page">
                        <i class="material-icons">home</i>
                        <h3>{{ $resource::label() }}</h3>
                    </a>
                </router-link>
            @endforeach
        @endif
    @endforeach
@endif
