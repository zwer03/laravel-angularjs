@extends('layouts.app')

@section('title', '| Configuration')

@section('content')
<div class="container">
    <div class="col">
        <h1><i class="fa fa-cog"></i> Configuration Administration <a href="{{ route('configurations.index') }}" class="btn btn-default pull-right"></h1>
        <hr>
        <a href="{{ route('configurations.create') }}" class="btn btn-success float-right my-2">Add Configuration</a>
        Page {{ $configurations->currentPage() }} of {{ $configurations->lastPage() }}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Value</th>
                        <th>Date/Time Added</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($configurations as $configuration)
                    <tr>

                        <td>{{ $configuration->id }}</td>
                        <td>{{ $configuration->name }}</td>
                        <td>{{ $configuration->description }}</td>
                        <td>{{ $configuration->created_at->format('F d, Y h:ia') }}</td>
                        <td>{{ $configuration->value }}</td>
                        <td>
                            <a href="{{ route('configurations.edit', $configuration->id) }}" class="btn btn-info btn-md btn-block pull-left" style="margin-right: 3px;">Edit</a>

                            {!! Form::open(['method' => 'DELETE', 'route' => ['configurations.destroy', $configuration->id] ]) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-md btn-block', 'onclick'=>"return confirm('Are you sure?')"]) !!}
                            {!! Form::close() !!}

                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
        <div class="text-center">
            {!! $configurations->links() !!}
        </div>
    </div>
</div>
@endsection