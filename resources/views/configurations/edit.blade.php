@extends('layouts.app')

@section('title', '| Edit Configuration')

@section('content')
<div class="container">
    <div class='col'>

        <h1><i class='fa fa-edit'></i> Edit {{$configuration->name}}</h1>
        <hr>

        {{ Form::model($configuration, array('route' => array('configurations.update', $configuration->id), 'method' => 'PUT')) }}{{-- Form model binding to automatically populate our fields with configuration data --}}

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

        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>
</div>
@endsection