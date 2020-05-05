@extends('layouts.app')

@section('title', '| SMS Template')

@section('content')
<div class="container">
    <div class="col">
        <h1><i class="fa fa-sms"></i> SMS Template Administration <a href="{{ route('sms_templates.index') }}" class="btn btn-default pull-right"></h1>
        <hr>
        <a href="{{ route('sms_templates.create') }}" class="btn btn-success float-right my-2">Add SMS Template</a>
        Page {{ $sms_templates->currentPage() }} of {{ $sms_templates->lastPage() }}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Description</th>
                        <th>Content</th>
                        <th>Date/Time Added</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($sms_templates as $sms_template)
                    <tr>

                        <td>{{ $sms_template->id }}</td>
                        <td>{{ $sms_template->subject }}</td>
                        <td>{{ $sms_template->description }}</td>
                        <td>{{ $sms_template->content }}</td>
                        <td>{{ $sms_template->created_at->format('F d, Y h:ia') }}</td>
                        <td>
                            <a href="{{ route('sms_templates.edit', $sms_template->id) }}" class="btn btn-info btn-md btn-block pull-left" style="margin-right: 3px;">Edit</a>

                            {!! Form::open(['method' => 'DELETE', 'route' => ['sms_templates.destroy', $sms_template->id] ]) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-md btn-block', 'onclick'=>"return confirm('Are you sure?')"]) !!}
                            {!! Form::close() !!}

                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
        <div class="text-center">
            {!! $sms_templates->links() !!}
        </div>
    </div>
</div>
@endsection