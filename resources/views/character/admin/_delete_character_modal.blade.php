<<<<<<< HEAD
{!! Form::open(['url' => $isMyo ? 'admin/myo/' . $character->id . '/delete' : 'admin/character/' . $character->slug . '/delete']) !!}
<p>This will delete the entire character and its images. <strong>This data will not be retrievable.</strong> </p>
<p>If you're looking for a less permanent option, you can set the character to not viewable and it will be hidden from public view.</p>
<p>Are you sure you want to do this?</p>

<div class="text-right">
    {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
</div>
=======
{!! Form::open(['url' => $isMyo ? 'admin/myo/'.$character->id.'/delete' : 'admin/character/'.$character->slug.'/delete']) !!}
    <p>This will delete the entire {{__('lorekeeper.character')}} and its images. <strong>This data will not be retrievable.</strong> </p>
    <p>If you're looking for a less permanent option, you can set the {{__('lorekeeper.character')}} to not viewable and it will be hidden from public view.</p>
    <p>Are you sure you want to do this?</p>

    <div class="text-right">
        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
    </div>
>>>>>>> 7741e9cbbdc31ea79be2d1892e9fa2efabce4cec
{!! Form::close() !!}
