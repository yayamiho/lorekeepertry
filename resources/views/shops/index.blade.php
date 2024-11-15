@extends('shops.layout')

@section('shops-title')
    Shop Index
@endsection

@section('shops-content')
    {!! breadcrumbs(['Shops' => 'shops']) !!}

    <h1>
        Shops
    </h1>

    <div class="row shops-row">
        @foreach ($shops as $shop)
            @if ($shop->is_staff)
                @if (auth::check() && auth::user()->isstaff)
                    @include('shops._shop')
                @endif
                <div class="shop-name mt-1">
                    <a href="{{ $shop->url }}" class="h5 mb-0">{{ $shop->name }}</a>
                </div>
            </div>
            <div class="shop-name mt-1">
                <a href="{{ $shop->url }}" class="h5 mb-0">{{ $shop->name }}</a>
            </div>
        </div>
    @endforeach
    <div class="col-md-3 col-6 mb-3 text-center">
        <div class="shop-image">
            <a href="{{ url('shops/donation-shop') }}"><img src="{{ asset('images/donation_shop.png') }}" /></a>
        </div>
        <div class="shop-name mt-1">
            <a href="{{ url('shops/donation-shop') }}" class="h5 mb-0">Donation Shop</a>
        </div>
            @else
                @include('shops._shop')
            @endif
        @endforeach
    </div>
</div>

@endsection
