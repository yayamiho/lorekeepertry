<ul>
    <li class="sidebar-header"><a href="{{ $user->url }}" class="card-link">{{ Illuminate\Support\Str::limit($user->name, 10, $end = '...') }}</a></li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Gallery</div>
        <div class="sidebar-item"><a href="{{ $user->url . '/gallery' }}" class="{{ set_active('user/' . $user->name . '/gallery*') }}">Gallery</a></div>
        <div class="sidebar-item"><a href="{{ $user->url . '/favorites' }}" class="{{ set_active('user/' . $user->name . '/favorites*') }}">Favorites</a></div>
        <div class="sidebar-item"><a href="{{ $user->url . '/favorites/own-characters' }}" class="{{ set_active('user/' . $user->name . '/favorites/own-characters*') }}">Own Character Favorites</a></div>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">User</div>
        <div class="sidebar-item"><a href="{{ $user->url . '/aliases' }}" class="{{ set_active('user/' . $user->name . '/aliases*') }}">Aliases</a></div>
        <div class="sidebar-item"><a href="{{ $user->url . '/characters' }}" class="{{ set_active('user/' . $user->name . '/characters*') }}">Characters</a></div>
        @if (isset($sublists) && $sublists->count() > 0)
            @foreach ($sublists as $sublist)
                <div class="sidebar-item"><a href="{{ $user->url . '/sublist/' . $sublist->key }}" class="{{ set_active('user/' . $user->name . '/sublist/' . $sublist->key) }}">{{ $sublist->name }}</a></div>
            @endforeach
        @endif
<<<<<<< HEAD
        <div class="sidebar-item"><a href="{{ $user->url . '/myos' }}" class="{{ set_active('user/' . $user->name . '/myos*') }}">MYO Slots</a></div>
        <div class="sidebar-item"><a href="{{ $user->url . '/inventory' }}" class="{{ set_active('user/' . $user->name . '/inventory*') }}">Inventory</a></div>
        <div class="sidebar-item"><a href="{{ $user->url . '/bank' }}" class="{{ set_active('user/' . $user->name . '/bank*') }}">Bank</a></div>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">History</div>
        <div class="sidebar-item"><a href="{{ $user->url . '/ownership' }}" class="{{ set_active('user/' . $user->name . '/ownership*') }}">Ownership History</a></div>
        <div class="sidebar-item"><a href="{{ $user->url . '/item-logs' }}" class="{{ set_active('user/' . $user->name . '/item-logs*') }}">Item Logs</a></div>
        <div class="sidebar-item"><a href="{{ $user->url . '/currency-logs' }}" class="{{ set_active('user/' . $user->name . '/currency-logs*') }}">Currency Logs</a></div>
        <div class="sidebar-item"><a href="{{ $user->url . '/submissions' }}" class="{{ set_active('user/' . $user->name . '/submissions*') }}">Submissions</a></div>
=======
        <div class="sidebar-item"><a href="{{ $user->url.'/myos' }}" class="{{ set_active('user/'.$user->name.'/myos*') }}">MYO Slots</a></div>
        <div class="sidebar-item"><a href="{{ $user->url.'/inventory' }}" class="{{ set_active('user/'.$user->name.'/inventory*') }}">Inventory</a></div>
        <div class="sidebar-item"><a href="{{ $user->url.'/'.__('awards.awardcase') }}" class="{{ set_active('user/'.$user->name.'/awardcase*') }}">{{ucfirst(ucfirst(__('awards.awardcase')))}}</a></div>
        <div class="sidebar-item"><a href="{{ $user->url.'/bank' }}" class="{{ set_active('user/'.$user->name.'/bank*') }}">Bank</a></div>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">History</div>
        <div class="sidebar-item"><a href="{{ $user->url.'/ownership' }}" class="{{ $user->url.'/ownership*' }}">Ownership History</a></div>
        <div class="sidebar-item"><a href="{{ $user->url.'/item-logs' }}" class="{{ $user->url.'/currency-logs*' }}">Item Logs</a></div>
        <div class="sidebar-item"><a href="{{ $user->url.'/currency-logs' }}" class="{{ set_active($user->url.'/currency-logs*') }}">Currency Logs</a></div>
        <div class="sidebar-item"><a href="{{ $user->url.'/'.__('awards.award').'-logs' }}" class="{{ set_active($user->url.'/award-logs*') }}">{{ucfirst(ucfirst(__('awards.award')))}} Logs</a></div>
        <div class="sidebar-item"><a href="{{ $user->url.'/submissions' }}" class="{{ set_active($user->url.'/submissions*') }}">Submissions</a></div>
>>>>>>> 7741e9cbbdc31ea79be2d1892e9fa2efabce4cec
    </li>

    @if (Auth::check() && Auth::user()->hasPower('edit_user_info'))
        <li class="sidebar-section">
            <div class="sidebar-section-header">Admin</div>
            <div class="sidebar-item"><a href="{{ $user->adminUrl }}">Edit User</a></div>
        </li>
    @endif
</ul>
