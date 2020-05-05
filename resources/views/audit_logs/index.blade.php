@extends('layouts.app')

@section('title', '| Audit Logs')

@section('content')
<div class="container">
    <div class="col">
        <h1><i class="fas fa-book"></i> Audit Logs Administration <a href="{{ route('audit_logs.index') }}" class="btn btn-default pull-right"></a></h1>
        <hr>
        Page {{ $audit_logs->currentPage() }} of {{ $audit_logs->lastPage() }}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>URL</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>Remarks</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($audit_logs as $audit_log)
                    <tr>
                        <td>{{ $audit_log->id }}</td>
                        <td>{{ $audit_log->url }}</td>
                        <td>{{ $audit_log->action }}</td>
                        <td>{{ $audit_log->ip_address }}</td>
                        <td>{{ $audit_log->remarks }}</td>
                        <td>{{ $audit_log->datetime }}</td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
        <div class="text-center">
            {!! $audit_logs->links() !!}
        </div>
    </div>
</div>
@endsection