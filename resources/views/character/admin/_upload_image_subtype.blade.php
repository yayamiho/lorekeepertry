{!! Form::label(ucfirst(__('lorekeeper.subtype')).' (Optional)') !!}
{!! Form::select('subtype_id', $subtypes, old('subtype_id') ? : $subtype, ['class' => 'form-control', 'id' => 'subtype']) !!}
