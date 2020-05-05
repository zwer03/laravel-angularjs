@extends('layouts.app')

@section('title', '| Permissions')

@section('content')
<div class="container">
    <div class="col">
        <h1><i class="fa fa-key"></i>Available Permissions

        <a href="{{ route('users.index') }}" class="btn btn-default pull-right">Users</a>
        <a href="{{ route('roles.index') }}" class="btn btn-default pull-right">Roles</a></h1>
        <hr>
        <a href="{{ URL::to('permissions/create') }}" class="btn btn-success float-right">Add Permission</a>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Permissions</th>
                        <th>Operation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                    <tr>
                        <td>{{ $permission->name }}</td> 
                        <td>
                        <a href="{{ URL::to('permissions/'.$permission->id.'/edit') }}" class="btn btn-info btn-md btn-block pull-left" style="margin-right: 3px;">Edit</a>

                        {!! Form::open(['method' => 'DELETE', 'route' => ['permissions.destroy', $permission->id] ]) !!}
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