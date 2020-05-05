@extends('layouts.app')

@section('title', '| Consultant Type')

@section('content')
<div class="container">
    <div class="col">
        <h1><i class="fa fa-user-md"></i> Consultant Type Administration <a href="{{ route('consultant_types.index') }}" class="btn btn-default pull-right"></h1>
        <hr>
        <a href="{{ route('consultant_types.create') }}" class="btn btn-success float-right my-2">Add Consultant Type</a>
        Page {{ $consultant_types->currentPage() }} of {{ $consultant_types->lastPage() }}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>PF Amount</th>
                        <th>Date/Time Added</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($consultant_types as $consultant_type)
                    <tr>

                        <td>{{ $consultant_type->external_id }}</td>
                        <td>{{ $consultant_type->name }}</td>
                        <td>{{ $consultant_type->default_pf_amount }}</td>
                        <td>{{ $consultant_type->created_at->format('F d, Y h:ia') }}</td>
                        <td>
                            <a href="{{ route('consultant_types.edit', $consultant_type->id) }}" class="btn btn-info btn-md btn-block pull-left" style="margin-right: 3px;">Edit</a>

                            {!! Form::open(['method' => 'DELETE', 'route' => ['consultant_types.destroy', $consultant_type->id] ]) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-md btn-block', 'onclick'=>"return confirm('Are you sure?')"]) !!}
                            {!! Form::close() !!}

                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
        <div class="text-center">
            {!! $consultant_types->links() !!}
        </div>
    </div>
</div>
@endsection