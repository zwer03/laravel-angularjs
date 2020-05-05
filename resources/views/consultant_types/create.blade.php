@extends('layouts.app')

@section('title', '| Add Consultant Type')

@section('content')
<div class="container">
    <div class='col'>

        <h1><i class='fa fa-plus'></i> Add Consultant Type</h1>
        <hr>

        {{ Form::open(array('url' => 'consultant_types')) }}

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

        {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>
</div>
@endsection