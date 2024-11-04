@if ($subtype)
    {!! Form::open(['url' => 'admin/data/subtypes/delete/' . $subtype->id]) !!}

    <p>You are about to delete the {{ __('lorekeeper.subtype') }} <strong>{{ $subtype->name }}</strong>. This is not reversible. If traits and/or characters that have this {{ __('lorekeeper.subtype') }} exist, you will not be able to delete this {{ __('lorekeeper.subtype') }}.</p>
    <p>Are you sure you want to delete <strong>{{ $subtype->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete '.ucfirst(__('lorekeeper.subtype')), ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
<<<<<<< HEAD
    Invalid subtype selected.
=======
    Invalid {{ __('lorekeeper.subtype') }} selected.
>>>>>>> 7741e9cbbdc31ea79be2d1892e9fa2efabce4cec
@endif
