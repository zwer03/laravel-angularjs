@extends('layouts.app')

@section('title', '| Reports')
{{-- {{dd($data['data']['0']->firstname)}} --}}
@section('content')
<div class="container">
    <div class="col">
        <h5 class="text-center">PF Summary Report</h5>
        <div class="table-responsive">
            <table class="table table-sm">
                <tbody>
                    <tr>
                      <form action="{{route('reports.pf_summary')}}" method="post">
                        @csrf
                        <td><input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="{{ date('Y-m-d', strtotime(now())) }}"></td>
                        <td><input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="{{ date('Y-m-d', strtotime(now())) }}"></td>
                        <td><button type="submit" class="btn btn-primary">Submit</button></td>
                      </form>
                    </tr>
                </tbody>
            </table>
            <table class="table table-hover">
              <thead>
                <tr>
                  <th class="col-md-auto" scope="col">
                    FIRST NAME
                  </th>
                  <th class="col-md-auto" scope="col">
                    LAST NAME
                  </th>
                  <th class="col-md-auto" scope="col">
                    DATE ADMITTED
                  </th>
                  <th class="col-md-auto" scope="col">
                    HOSPITALIZATION PLAN
                  </th>
                  <th class="col-md-auto">
                    PF AMOUNT
                  </th>
                </tr>
              </thead>
              <tbody>
                @foreach($data['data'] as $pf_summary)
                  <tr>
                    <td class="col-md-auto">{{$pf_summary->firstname}}</td>
                    <td class="col-md-auto">{{$pf_summary->lastname}}</td>
                    <td class="col-md-auto">{{$pf_summary->created_at}}</td>
                    <td class="col-md-auto">{{$pf_summary->hospitalization_plan}}</td>
                    <td class="col-md-auto text-right">{{$pf_summary->pf_amount}}</td>
                  </tr>
                @endforeach
              </tbody> 
            </table>
        </div>
    </div>
</div>
@endsection