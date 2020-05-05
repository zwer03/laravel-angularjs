@extends('layouts.app')

@section('title', '| Edit Configuration')

@section('content')
<div class="container">
    <div class='col'>

        <h1><i class='fa fa-edit'></i> Edit {{$consultant_type->name}}</h1>
        <hr>

        {{ Form::model($consultant_type, array('route' => array('consultant_types.update', $consultant_type->id), 'method' => 'PUT')) }}{{-- Form model binding to automatically populate our fields with consultant_type data --}}

        <div class="form-group">
            {{ Form::label('external_id', 'External ID') }}
            {{ Form::text('external_id', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('name', 'Name') }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('default_pf_amount', 'PF Amount') }}
            {{ Form::text('default_pf_amount', null, array('class' => 'form-control')) }}
        </div>

        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>
</div>
@endsection