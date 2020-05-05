@extends('layouts.app')

@section('title', '| Add SMS Templates')

@section('content')
<div class="container">
    <div class='col'>

        <h1><i class='fa fa-sms'></i> Add SMS Templates</h1>
        <hr>

        {{ Form::open(array('url' => 'sms_templates')) }}

        <div class="form-group">
            {{ Form::label('subject', 'Subject') }}
            {{ Form::text('subject', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('description', 'Description') }}
            {{ Form::text('description', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('content', 'Content') }}
            {{ Form::text('content', null, array('class' => 'form-control')) }}
        </div>

        {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>
</div>
@endsection