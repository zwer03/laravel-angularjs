@extends('layouts.app')

@section('title', '| Roles')

@section('content')
<div class="container">
    <div class="col">
        <h1><i class="fa fa-key"></i> Roles

        <a href="{{ route('users.index') }}" class="btn btn-default pull-right">Users</a>
        <a href="{{ route('permissions.index') }}" class="btn btn-default pull-right">Permissions</a></h1>
        <hr>
        <a href="{{ URL::to('roles/create') }}" class="btn btn-success float-right my-2">Add Role</a>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Operation</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($roles as $role)
                    <tr>

                        <td>{{ $role->name }}</td>

                        <td>{{ str_replace(array('[',']','"'),'', $role->permissions()->pluck('name')) }}</td>{{-- Retrieve array of permissions associated to a role and convert to string --}}
                        <td>
                        <a href="{{ URL::to('roles/'.$role->id.'/edit') }}" class="btn btn-info btn-md btn-block pull-left" style="margin-right: 3px;">Edit</a>

                        {!! Form::open(['method' => 'DELETE', 'route' => ['roles.destroy', $role->id] ]) !!}
                        {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-md btn-block', 'onclick'=>"return confirm('Are you sure?')"]) !!}
                        {!! Form::close() !!}

                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
</div>
@endsection