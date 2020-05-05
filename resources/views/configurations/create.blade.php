@extends('layouts.app')

@section('title', '| Add Configuration')

@section('content')
<div class="container">
    <div class='col'>

        <h1><i class='fa fa-plus'></i> Add Configuration</h1>
        <hr>

        {{ Form::open(array('url' => 'configurations')) }}

        <div class="form-group">
            {{ Form::label('id', 'ID') }}
            {{ Form::text('id', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('name', 'Name') }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('description', 'Description') }}
            {{ Form::text('description', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('value', 'value') }}
            {{ Form::text('value', null, array('class' => 'form-control')) }}
        </div>

        {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>
</div>
@endsection