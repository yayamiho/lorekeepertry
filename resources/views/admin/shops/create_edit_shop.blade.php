@extends('admin.layout')

@section('admin-title') Shops @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Shops' => 'admin/data/shops', ($shop->id ? 'Edit' : 'Create').' Shop' => $shop->id ? 'admin/data/shops/edit/'.$shop->id : 'admin/data/shops/create']) !!}

<h1>{{ $shop->id ? 'Edit' : 'Create' }} Shop
    @if($shop->id)
        ({!! $shop->displayName !!})
        <a href="#" class="btn btn-danger float-right delete-shop-button">Delete Shop</a>
    @endif
</h1>

{!! Form::open(['url' => $shop->id ? 'admin/data/shops/edit/'.$shop->id : 'admin/data/shops/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $shop->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Shop Image (Optional)') !!} {!! add_help('This image is used on the shop index and on the shop page as a header.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: None (Choose a standard size for all shop images)</div>
    @if($shop->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $shop->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_active', 1, $shop->id ? $shop->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the shop will not be visible to regular users.') !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_staff', 1, $shop->id ? $shop->is_staff : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_staff', 'For Staff?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, the shop will not be visible to regular users, only staff.') !!}
</div>

<div class="form-group">
    {!! Form::checkbox('use_coupons', 1, $shop->id ? $shop->use_coupons : 0, ['class' => 'form-check-label', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('use_coupons', 'Allow Coupons?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Note that ALL coupons will be allowed to be used.') !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_fto', 1, $shop->id ? $shop->is_fto : 0, ['class' => 'form-check-label', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_fto', 'FTO Only?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Only users who are currently FTO and staff can enter.') !!}
</div>


<div class="text-right">
    {!! Form::submit($shop->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if($shop->id)
{!! Form::open(['url' => 'admin/data/shops/restrictions/'.$shop->id]) !!}
    <h3>Restrict Shop</h3>
        <div class="form-group">
            {!! Form::checkbox('is_restricted', 1, $shop->is_restricted, ['class' => 'is-restricted-class form-check-label', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_restricted', 'Should this shop require an item to enter?', ['class' => 'is-restricted-label form-check-label ml-3']) !!} {!! add_help('If turned on, the shop will cannot be entered unless the user currently owns all required items.') !!}
        </div>

    <div class="br-form-group" style="display: none">
        <div><a href="#" class="btn btn-primary mb-3" id="add-feature">Add Item Requirement</a></div>

        <div class="form-group">
                @foreach($shop->limits as $limit)
                <div class="row mb-2">
                    {!! Form::label('item_id', 'Item', ['class' => 'col-form-label']) !!}
                        <div class="col-4">
                            {!! Form::select('item_id[]', $items, $limit->item_id, ['class' => 'form-control', 'placeholder' => 'Select Item']) !!}
                        </div>
                    <a href="#" class="remove-feature btn btn-danger">Remove</a>
                </div>
                @endforeach
        </div>
            <div id="featureList" class="form-group">
        </div>
    </div>
    <div class="text-right">
        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <h3>Shop Stock</h3>
    <div class="text-right mb-3">
        <a href="#" class="add-stock-button btn btn-outline-primary">Add Stock</a>
    </div>
    <div id="shopStock">
        <div class="row col-12">
        @foreach($shop->stock as $stock)
        <div class="col-4">
            <div class="card p-3 my-1">
                <div><a href="{{ $stock->item->idUrl }}"><strong>{{ $stock->item->name }} - {{ $stock->stock_type }}</strong></a></div>
                <div><strong>Cost: </strong> {!! $stock->currency->display($stock->cost) !!}</div>
                @if($stock->is_limited_stock) <div>Stock: {{ $stock->quantity }}</div> @endif
                @if($stock->purchase_limit) <div class="text-danger">Max {{ $stock->purchase_limit }} per user</div> @endif
                <div class="text-right">
                    <button class="btn btn-primary" onclick="editStock({{$stock->id}})">
                        {{-- pencil icon --}}
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <div class="btn btn-danger" onclick="deleteStock({{$stock->id}})">
                        {{-- trash icon --}}
                        <i class="fas fa-trash"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

<div class="feature-row mb-2 hide">
    {!! Form::label('item_id', 'Item', ['class' => 'col-form-label']) !!}
        <div class="col-4">
            {!! Form::select('item_id[]', $items, null, ['class' => 'form-control', 'placeholder' => 'Select Item']) !!}
        </div>
        <a href="#" class="remove-feature btn btn-danger">Remove</a>
</div>
@endsection

@section('scripts')
@parent
<script>
    // edit stock function
    function editStock(id) {
        loadModal("{{ url('admin/data/shops/stock/edit') }}/" + id, 'Edit Stock');
    }
    function deleteStock(id) {
        loadModal("{{ url('admin/data/shops/stock/delete') }}/" + id, 'Delete Stock');
    }
$( document ).ready(function() {

    $('.delete-shop-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/shops/delete') }}/{{ $shop->id }}", 'Delete Shop');
    });
    $('.add-stock-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/shops/stock') }}/{{ $shop->id }}", 'Add Stock');
    });

    $('#add-feature').on('click', function(e) {
        e.preventDefault();
        addFeatureRow();
    });
    $('.remove-feature').on('click', function(e) {
        e.preventDefault();
        removeFeatureRow($(this));
    });

    function addFeatureRow() {
        var $clone = $('.feature-row').clone();
        $('#featureList').append($clone);
        $clone.removeClass('hide feature-row');
        $clone.addClass('d-flex');
        $clone.find('.remove-feature').on('click', function(e) {
            e.preventDefault();
            removeFeatureRow($(this));
        })
        $clone.find('.feature-select').selectize();
    }
    function removeFeatureRow($trigger) {
        $trigger.parent().remove();
    }

    $('.is-restricted-class').change(function(e){
            console.log(this.checked)
            $('.br-form-group').css('display',this.checked ? 'block' : 'none')
                })
            $('.br-form-group').css('display',$('.is-restricted-class').prop('checked') ? 'block' : 'none')
});
    
</script>
@endsection