<dropdown-trigger class="h-9 flex items-center">
    @isset($user->profile_photo)
        <img
            src="{{ asset("storage/{$user->profile_photo}") }}"
            class="rounded-full w-8 h-8 mr-3"
        />
    @endisset

    <span class="text-90">
        {{ $user->name ?? $user->email ?? __('Nova User') }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="200" direction="rtl">
    <ul class="list-reset">
        <li>
            <a href="{{ url("/resources/users/{$user->id}") }}" class="block no-underline text-90 hover:bg-30 p-3">
                Profile
            </a>
        </li>
        <li>
            <a href="{{ route('nova.logout') }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Logout') }}
            </a>
        </li>
    </ul>
</dropdown-menu>
