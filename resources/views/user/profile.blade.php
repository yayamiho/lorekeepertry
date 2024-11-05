@extends('user.layout', ['user' => isset($user) ? $user : null])

@section('profile-title')
    {{ $user->name }}'s Profile
@endsection

@section('meta-img')
    {{ $user->avatarUrl }}
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url]) !!}

@include('widgets._awardcase_feature', ['target' => $user, 'count' => Config::get('lorekeeper.extensions.awards.user_featured'), 'float' => false])

    @if ($user->is_banned)
        <div class="alert alert-danger">This user has been banned.</div>
    @endif
</h1>
<div class="row">
    <div class="col-md mb-4">
        <div class="row">
            <div class="row col-md-6">
                <div class="col-md-2 col-4"><h5>Alias</h5></div>
                <div class="col-md-10 col-8">{!! $user->displayAlias !!}</div>
            </div>
            <div class="row col-md-6">
                <div class="col-md-2 col-4"><h5>Joined</h5></div>
                <div class="col-md-10 col-8">{!! format_date($user->created_at, false) !!} ({{ $user->created_at->diffForHumans() }})</div>
            </div>
            <div class="row col-md-6">
                <div class="col-md-2 col-4"><h5>Rank</h5></div>
                <div class="col-md-10 col-8">{!! $user->rank->displayName !!} {!! add_help($user->rank->parsed_description) !!}</div>
            </div>
            @if($user->birthdayDisplay && isset($user->birthday))
                <div class="row col-md-6">
                    <div class="col-md-2 col-4"><h5>Birthday</h5></div>
                    <div class="col-md-10 col-8">{!! $user->birthdayDisplay !!}</div>
                </div>
            @endif
        </div>
    </div>
    @if(Settings::get('event_teams') && $user->settings->team)
        <div class="col-md-2 text-center">
            <a href="{{ url('event-tracking') }}">
                @if($user->settings->team->has_image)
                    <img src="{{ $user->settings->team->imageUrl }}" class="mw-100"/>
                @endif
                <h5>{{ $user->settings->team->name }}</h5>
            </a>
        </div>
    @endif
</div>

@if(isset($user->profile->parsed_text))
    <div class="card mb-3" style="clear:both;">
        <div class="card-body">
            {!! $user->profile->parsed_text !!}
        </div>
    </div>
@endif


<div class="card-deck mb-4 profile-assets" style="clear:both;">
    <div class="card profile-currencies profile-assets-card">
        <div class="card-body text-center">
            <h5 class="card-title">Bank</h5>
            <div class="profile-assets-content">
                @foreach($user->getCurrencies(false) as $currency)
                    <div>{!! $currency->display($currency->quantity) !!}</div>
                @endforeach
            </div>
            <div class="row col-md-6">
                <div class="col-md-2 col-4">
                    <h5>Joined</h5>
                </div>
                <div class="col-md-10 col-8">{!! format_date($user->created_at, false) !!} ({{ $user->created_at->diffForHumans() }})</div>
            </div>
            <div class="row col-md-6">
                <div class="col-md-2 col-4">
                    <h5>Rank</h5>
                </div>
                <div class="col-md-10 col-8">{!! $user->rank->displayName !!} {!! add_help($user->rank->parsed_description) !!}</div>
            </div>
            @if ($user->birthdayDisplay && isset($user->birthday))
                <div class="row col-md-6">
                    <div class="col-md-2 col-4">
                        <h5>Birthday</h5>
                    </div>
                    <div class="col-md-10 col-8">{!! $user->birthdayDisplay !!}</div>
                </div>
            @endif
            @if (isset($user->border) || isset($user->borderVariant))
                <div class="row col-sm-5">
                    <div class="col-md-3 col-4">
                        <h5>Border</h5>
                    </div>
                    <div class="col-md-9 col-8">
                        <a href="{{ $user->borderVariant ? $user->borderVariant->parent->idUrl : $user->border->idUrl }}">
                            {!! $user->borderVariant ? $user->borderVariant->parent->name : $user->border->name !!} @if ($user->borderVariant)
                                ({{ $user->borderVariant->name }})
                            @endif
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
    <div class="card mb-3">
        <div class="card-body text-center">
            <h5 class="card-title">{{ ucfirst(__('awards.awards')) }}</h5>
            <div class="card-body">
                @if(count($awards))
                    <div class="row">
                        @foreach($awards as $award)
                            <div class="col-md-3 col-6 profile-inventory-item">
                                @if($award->imageUrl)
                                    <img src="{{ $award->imageUrl }}" data-toggle="tooltip" title="{{ $award->name }}" />
                                @else
                                    <p>{{ $award->name }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div>No {{ __('awards.awards') }} earned.</div>
                @endif
            </div>
            <div class="text-right"><a href="{{ $user->url.'/'.__('awards.awardcase') }}">View all...</a></div>
        </div>
    </div>

<h2>
    <a href="{{ $user->url.'/characters' }}">Characters</a>
    @if(isset($sublists) && $sublists->count() > 0)
        @foreach($sublists as $sublist)
        / <a href="{{ $user->url.'/sublist/'.$sublist->key }}">{{ $sublist->name }}</a>
        @endforeach
    @endif

    @if ($user->is_deactivated)
        <div class="alert alert-info text-center">
            <h1>{!! $user->displayName !!}</h1>
            <p>This account is currently deactivated, be it by staff or the user's own action. All information herein is hidden until the account is reactivated.</p>
            @if (Auth::check() && Auth::user()->isStaff)
                <p class="mb-0">As you are staff, you can see the profile contents below and the sidebar contents.</p>
            @endif
        </div>
    @endif

    @if (!$user->is_deactivated || (Auth::check() && Auth::user()->isStaff))
        @include('user._profile_content', ['user' => $user, 'deactivated' => $user->is_deactivated])
    @endif


    @if (isset($user->profile->parsed_text))
        <div class="card mb-3" style="clear:both;">
            <div class="card-body">
                {!! $user->profile->parsed_text !!}
            </div>
        </div>
    @endif

    <div class="card-deck mb-4 profile-assets" style="clear:both;">
        <div class="card profile-currencies profile-assets-card">
            <div class="card-body text-center">
                <h5 class="card-title">Bank</h5>
                <div class="profile-assets-content">
                    @foreach ($user->getCurrencies(false) as $currency)
                        <div>{!! $currency->display($currency->quantity) !!}</div>
                    @endforeach
                </div>
                <div class="text-right"><a href="{{ $user->url . '/bank' }}">View all...</a></div>
            </div>
        </div>
        <div class="card profile-inventory profile-assets-card">
            <div class="card-body text-center">
                <h5 class="card-title">Inventory</h5>
                <div class="profile-assets-content">
                    @if (count($items))
                        <div class="row">
                            @foreach ($items as $item)
                                <div class="col-md-3 col-6 profile-inventory-item">
                                    @if ($item->imageUrl)
                                        <img src="{{ $item->imageUrl }}" data-toggle="tooltip" title="{{ $item->name }}" alt="{{ $item->name }}" />
                                    @else
                                        <p>{{ $item->name }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div>No items owned.</div>
                    @endif
                </div>
                <div class="text-right"><a href="{{ $user->url . '/inventory' }}">View all...</a></div>
            </div>
        </div>
    </div>

    <h2>
        <a href="{{ $user->url . '/characters' }}">Characters</a>
        @if (isset($sublists) && $sublists->count() > 0)
            @foreach ($sublists as $sublist)
                / <a href="{{ $user->url . '/sublist/' . $sublist->key }}">{{ $sublist->name }}</a>
            @endforeach
        @endif
    </h2>

    @foreach ($characters->take(4)->get()->chunk(4) as $chunk)
        <div class="row mb-4">
            @foreach ($chunk as $character)
                <div class="col-md-3 col-6 text-center">
                    <div>
                        <a href="{{ $character->url }}"><img src="{{ $character->image->thumbnailUrl }}" class="img-thumbnail" alt="{{ $character->fullName }}" /></a>
                    </div>
                    <div class="mt-1">
                        <a href="{{ $character->url }}" class="h5 mb-0">
                            @if (!$character->is_visible)
                                <i class="fas fa-eye-slash"></i>
                            @endif {{ $character->fullName }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="text-right"><a href="{{ $user->url . '/characters' }}">View all...</a></div>
    <hr>
    <br><br>

    @comments(['model' => $user->profile, 'perPage' => 5])
@endsection
