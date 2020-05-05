@extends('layouts.app')

@section('title', '| Edit SMS Template')

@section('content')
<div class="container">
    <div class='col'>

        <h1><i class='fa fa-edit'></i> Edit {{$sms_template->subject}}</h1>
        <hr>

        {{ Form::model($sms_template, array('route' => array('sms_templates.update', $sms_template->id), 'method' => 'PUT')) }}{{-- Form model binding to automatically populate our fields with sms_template data --}}

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

        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>
</div>
@endsection