@extends('layouts.app')

@section('title', '| Users')

@section('content')
<div class="container">
    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col-8">
                     <h1><i class="fa fa-users"></i>User Administration </h1>
                </div>
                @role('superadmin')
                <div class="col-2">
                    <a href="{{ route('roles.index') }}" class="btn btn-default pull-right"><i class="fa fa-key"></i> Roles</a>
                </div>
                <div class="col-2">
                    <a href="{{ route('permissions.index') }}" class="btn btn-default pull-right"><i class="fa fa-universal-access"></i> Permissions</a>
                </div>
                @endrole
            </div>
            <hr>
            <div class="row">
                    {{ Form::open(array('url' => route('users.search'), 'class' => 'form-inline')) }}
                    <div class="form-group col-6">
                        {{ Form::text('search_keyword', null, array('class' => 'form-control')) }}
                    </div>
                    <div class="form-group col-3">
                        {{ Form::submit('Search', array('class' => 'btn btn-primary')) }}
                    </div>
                    <div class="form-group col-3 text-align-right">
                        <a href="{{ route('users.create') }}" class="btn btn-success" role="button">Add User</a>
                    </div>
                    {{ Form::close() }}
            </div>
            <div class="row">
                Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Date/Time Added</th>
                                <th>User Roles</th>
                                <th>Operations</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($users as $user)
                            <tr>

                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->created_at->format('F d, Y h:ia') }}</td>
                                <td>{{  $user->roles()->pluck('name')->implode(' ') }}</td>{{-- Retrieve array of roles associated to a user and convert to string --}}
                                <td>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-info btn-md btn-block pull-left" style="margin-right: 3px;">Edit</a>

                                {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id] ]) !!}
                                {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-md btn-block', 'onclick'=>"return confirm('Are you sure?')"]) !!}
                                {!! Form::close() !!}

                                </td>
                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
                <div class="text-center">
                    {!! $users->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection