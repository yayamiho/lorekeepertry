@extends('admin.layout')

@section('admin-title')
    Species
@endsection

@section('admin-content')
<<<<<<< HEAD
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Species' => 'admin/data/species']) !!}

    <h1>Species</h1>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/species/create') }}"><i class="fas fa-plus"></i> Create New Species</a></div>
    @if (!count($specieses))
        <p>No species found.</p>
    @else
        <table class="table table-sm species-table">
            <thead>
                <tr>
                    <th>Species</th>
                    <th>Sub Masterlist</th>
                    <th></th>
=======
{!! breadcrumbs(['Admin Panel' => 'admin', ucfirst(__('lorekeeper.species')) => 'admin/data/species']) !!}

<h1>{{ ucfirst(__('lorekeeper.species')) }}</h1>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/species/create') }}"><i class="fas fa-plus"></i> Create New {{ ucfirst(__('lorekeeper.species')) }}</a></div>
@if(count($specieses))
    <table class="table table-sm species-table">
    <thead>
            <tr>
                <th>{{ ucfirst(__('lorekeeper.species')) }}</th>
                <th>Sub Masterlist</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="sortable" class="sortable">
            @foreach($specieses as $species)
                <tr class="sort-item" data-id="{{ $species->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $species->displayName !!}
                    </td>
                    <td>
                    @if(isset($species->sublist->name)) {{ $species->sublist->name  }} @else -- @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/species/edit/'.$species->id) }}" class="btn btn-primary">Edit</a>
                    </td>
>>>>>>> 7741e9cbbdc31ea79be2d1892e9fa2efabce4cec
                </tr>
            </thead>
            <tbody id="sortable" class="sortable">
                @foreach ($specieses as $species)
                    <tr class="sort-item" data-id="{{ $species->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            @if (!$species->is_visible)
                                <i class="fas fa-eye-slash mr-1"></i>
                            @endif
                            {!! $species->displayName !!}
                        </td>
                        <td>
                            @if (isset($species->sublist->name))
                                {{ $species->sublist->name }}
                            @else
                                --
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/species/edit/' . $species->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

<<<<<<< HEAD
        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/species/sort']) !!}
            {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    @endif
=======
    </table>
    <div class="mb-4">
        {!! Form::open(['url' => 'admin/data/species/sort']) !!}
        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>

    <div class="text-center mt-4 small text-muted">{{ count($specieses) }} {{ trans_choice('lorekeeper.specieses_', $specieses->count()) }} found.</div>
@endif
>>>>>>> 7741e9cbbdc31ea79be2d1892e9fa2efabce4cec

@endsection

@section('scripts')
<<<<<<< HEAD
    @parent
    <script>
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
=======
@parent
<script>

$( document ).ready(function() {
    $('.handle').on('click', function(e) {
        e.preventDefault();
    });
    $( "#sortable" ).sortable({
        items: '.sort-item',
        handle: ".handle",
        placeholder: "sortable-placeholder",
        stop: function( event, ui ) {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        },
        create: function() {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        }
    });
    $( "#sortable" ).disableSelection();
});
</script>
>>>>>>> 7741e9cbbdc31ea79be2d1892e9fa2efabce4cec
@endsection
