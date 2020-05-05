@extends('layouts.app')

@section('title', '| Edit User')

@section('content')
<div class="container">
    <div class='col'>

        <h1><i class='fa fa-user-edit'></i> Edit {{$user->name}}</h1>
        <hr>

        {{ Form::model($user, array('route' => array('users.update', $user->id), 'method' => 'PUT')) }}{{-- Form model binding to automatically populate our fields with user data --}}

        <div class="form-group">
            {{ Form::label('name', 'Name') }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('username', 'Username') }}
            {{ Form::text('username', null, array('class' => 'form-control', 'readonly')) }}
        </div>

        @role('superadmin')
        <h5><b>Give Role</b></h5>
        <div class='form-group'>
            @foreach ($roles as $role)
                {{ Form::checkbox('roles[]',  $role->id, $user->roles ) }}
                {{ Form::label($role->name, ucfirst($role->name)) }}<br>

            @endforeach
        </div>
        @endrole

        <div class="form-group">
            {{ Form::label('password', 'Password') }}<br>
            {{ Form::password('password', array('class' => 'form-control')) }}

        </div>

        <div class="form-group">
            {{ Form::label('password', 'Confirm Password') }}<br>
            {{ Form::password('password_confirmation', array('class' => 'form-control')) }}

        </div>

        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>
</div>
@endsection