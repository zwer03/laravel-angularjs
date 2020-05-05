@extends('layouts.app')
{{-- {{dd($data['external_id'])}} --}}
@section('content')
<div class="container">
<script src="{{ asset('js/auto_comma_text.js') }}"></script>
<script src="{{ asset('js/pf_validation.js') }}"></script>
<script src="{{ asset('js/angular.min.js') }}"></script>
<script src="{{ asset('js/angular-resource.min.js') }}"></script>
<script src="{{ asset('js/angularjs/app.module.js') }}"></script>
<script src="{{ asset('js/loading-bar.min.js') }}"></script>
<script src="{{ asset('js/angularjs/controllers/physicians.controller.js') }}"></script>
<link href="{{ asset('css/loading-bar.min.css') }}" rel="stylesheet">
{{-- BS toggle --}}
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

<div class="container pb-3" ng-app="mro" ng-controller="physiciansCtrl" ng-init="viewTransaction({{$data['external_id']}}, {{$data['patient_id']}}, {{$data['practitioner_id']}})">
    {{-- @{{transaction_details['PatientVisit']}} --}}
    <div class="card my-3">
        <div class="card-header text-white bg-primary">Patient Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col font-weight-bold">
                    Patient ID
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['patient_id']}}
                </div>
                <div class="w-100 d-block d-sm-none"></div>
                <div class="col font-weight-bold">
                    Admission No.
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['visit_number']}}
                </div>
            </div>
            <div class="border-top my-2"></div>
            <div class="row">
                <div class="col font-weight-bold">
                    Name
                </div>
                <div class="col" id="patient_name">
                    @{{transaction_details['PatientVisit']['px_last_name']}}, @{{transaction_details['PatientVisit']['px_first_name']}} @{{transaction_details['PatientVisit']['px_middle_name']}}
                </div>
                <div class="w-100 d-block d-sm-none"></div>
                <div class="col font-weight-bold">
                    Age/Gender
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['age']}} / @{{transaction_details['PatientVisit']['px_sex']}}
                </div>
            </div>
            <div class="border-top my-2"></div>
            <div class="row">
                <div class="col font-weight-bold">
                    Date of admission
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['admission_datetime']}}
                </div>
                <div class="w-100 d-block d-sm-none"></div>
                <div class="col font-weight-bold">
                    No. of Days Admitted
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['no_days_admitted']}}
                </div>
            </div>
            <div class="border-top my-2"></div>
            <div class="row">
                <div class="col font-weight-bold">
                    Date of Dicharge Order
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['mgh_datetime']}}
                </div>
                <div class="w-100 d-block d-sm-none"></div>
                <div class="col font-weight-bold">
                    Room & Bed
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['bed_room']}}
                </div>
            </div>
            <div class="border-top my-2"></div>
            <div class="row">
                <div class="col font-weight-bold">
                    Chief Complaint
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['chief_complaint']}}
                </div>
                <div class="w-100 d-block d-sm-none"></div>
                <div class="col font-weight-bold">
                    Running Balance
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['total_debit']}}
                </div>
            </div>
            <div class="border-top my-2"></div>
        </div>
    </div>
    <div class="card my-3">
        <div class="card-header text-white bg-primary">Other Information</div>
        <div class="card-body">
            <div class="alert alert-info d-none" id="pf_timer">
            </div>
            <div class="row">
                <div class="col font-weight-bold">
                    Hospitalization Plan
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['hospitalization_plan']}}
                </div>
                <div class="w-100 d-block d-sm-none"></div>
                <div class="col font-weight-bold">
                    PHIC Eligible
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['membership_id']=='1036'?'Yes':'N/A'}}
                </div>
            </div>
            <div class="border-top my-2"></div>
            <div class="row">
                <div class="col font-weight-bold">
                    HMO
                </div>
                <div class="col">
                    <ul class="list-group">
                        <li class="list-group-item" ng-repeat="hmo in transaction_details['PatientVisitHmo']">@{{hmo.name}}</li>
                    </ul>
                </div>
                <div class="w-100 d-block d-sm-none"></div>
                <div class="col font-weight-bold">
                    Medical Package(s)
                </div>
                <div class="col">
                    <ul class="list-group">
                        <li class="list-group-item" ng-repeat="mp in transaction_details['PatientVisitMedicalPackages']">@{{mp.name}}</li>
                    </ul>
                </div>
            </div>
            <div class="border-top my-2"></div>
            <div class="row">
                <div class="col font-weight-bold">
                    Consultant Role Type
                </div>
                <div class="col">
                    @{{transaction_details['PatientVisit']['consultant_type']}}
                </div>
                <div class="w-100 d-block d-sm-none"></div>
                <div class="col font-weight-bold" ng-if="transaction_details['PatientVisit']['status'] == 10">
                    Status
                </div>
                <div class="col" ng-if="transaction_details['PatientVisit']['status'] == 10">
                    <span class="font-weight-bold">Cancelled</span>
                </div>
            </div>
            <div class="border-top my-2"></div>
            <form id="professionalFee" action="{{route('physician.set_professional_fee')}}" method="post">
                <div class="row">
                    <div class="col pt-2 font-weight-bold">
                        PF Amount
                    </div>
                    <div class="col pt-2">
                        @csrf
                        <input type="hidden" name="data[PatientVisit][id]" value="@{{transaction_details['PatientVisit']['id']}}">
                        <input type="hidden" name="data[PatientCareProvider][id]" value="@{{transaction_details['PatientVisit']['pcp_id']}}">
                        <input type="hidden" name="data[PatientCareProvider][expiration_datetime]" value="@{{transaction_details['PatientVisit']['expiration_datetime']}}">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroupPrepend"><i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Patient with HMO will automatically compute by Billing."></i></span>
                            </div>
                            <input type="text" name="data[PatientCareProvider][pf_amount]" class="form-control" id="pf_amount" value="@{{transaction_details['PatientVisit']['pf_amount']}}" onkeypress="return CheckNumeric()" onkeyup="FormatCurrency(this)">
                        </div>
                        {{-- <div class="form-group">
                            <input type="text" name="data[PatientCareProvider][pf_amount]" class="form-control" id="pf_amount" value="@{{transaction_details['PatientVisit']['pf_amount']}}" onkeypress="return CheckNumeric()" onkeyup="FormatCurrency(this)"><i class="fas fa-info-circle"></i>
                        </div> --}}
                    </div>
                    <div class="w-100 d-block d-sm-none"></div>
                    <div class="col pt-2">
                        <span ng-click="toggleDisplayPf()" class="float-right">
                            <input type="checkbox" id="toggle-pf-display" data-toggle="toggle" data-onstyle="success">
                        </span>
                    </div>
                    <div class="col pt-2" ng-if="transaction_details['OtherPhysician'].length">
                        <div class="dropdown dropright">
                            <button type="button" class="btn btn-secondary btn-block dropdown-toggle" data-toggle="dropdown">
                            Other Physician
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" ng-repeat="physician in transaction_details['OtherPhysician']">@{{physician.lastname}}, @{{physician.firstname}} | @{{physician.consultant_type}} | @{{physician.pf_amount}}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-top my-2"></div>
                <div class="row">
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                </div>
            </form>
            <div class="border-top my-2"></div>
        </div>
    </div>
</div>
@endsection