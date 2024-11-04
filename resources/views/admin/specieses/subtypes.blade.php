@extends('admin.layout')

@section('admin-title')
    Subtypes
@endsection

@section('admin-content')
<<<<<<< HEAD
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Subtypes' => 'admin/data/subtypes']) !!}

    <h1>Subtypes</h1>

    <p>Subtypes are optional categories that can be added to species. Characters require a species, but do not require a subtype. Note that the sort order here reflects the sort order under the species name as well.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/subtypes/create') }}"><i class="fas fa-plus"></i> Create New Subtype</a></div>
    @if (!count($subtypes))
        <p>No subtypes found.</p>
    @else
        <table class="table table-sm subtypes-table">
            <tbody id="sortable" class="sortable">
                @foreach ($subtypes as $subtype)
                    <tr class="sort-item" data-id="{{ $subtype->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            @if (!$subtype->is_visible)
                                <i class="fas fa-eye-slash mr-1"></i>
                            @endif
                            {!! $subtype->displayName !!}
                        </td>
                        <td>
                            {!! $subtype->species->displayName !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/subtypes/edit/' . $subtype->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
=======
{!! breadcrumbs(['Admin Panel' => 'admin', __('lorekeeper.subtypes')  => 'admin/data/subtypes']) !!}

<h1>{{ __('lorekeeper.subtypes') }}</h1>

<p>{{ ucfirst(__('lorekeeper.subtypes')) }} are optional categories that can be added to species. Characters require a species, but do not require a {{ __('lorekeeper.subtype') }}. Note that the sort order here reflects the sort order under the species name as well.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/subtypes/create') }}"><i class="fas fa-plus"></i> Create New {{ ucfirst(__('lorekeeper.subtype')) }}</a></div>
@if(count($subtypes))
    <table class="table table-sm subtypes-table">
        <tbody id="sortable" class="sortable">
            @foreach($subtypes as $subtype)
                <tr class="sort-item" data-id="{{ $subtype->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $subtype->displayName !!}
                    </td>
                    <td>
                        {!! $subtype->species->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/subtypes/edit/'.$subtype->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
>>>>>>> 7741e9cbbdc31ea79be2d1892e9fa2efabce4cec

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/subtypes/sort']) !!}
            {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    @endif

<div class="text-center mt-4 small text-muted">{{ $subtypes->count() }} {{ trans_choice('lorekeeper.subtypes_', $subtypes->count()) }} found.</div>

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
