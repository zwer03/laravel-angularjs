@extends('layouts.app')

@section('content')
{{-- {{ route('physician.view_transaction') }} --}}
<script src="{{ asset('js/modernizr.min.js') }}"></script>
<script src="{{ asset('js/accordion_table.js') }}"></script>
<script src="{{ asset('js/angular.min.js') }}"></script>
<script src="{{ asset('js/angular-resource.min.js') }}"></script>
<script src="{{ asset('js/angularjs/app.module.js') }}"></script>
<script src="{{ asset('js/loading-bar.min.js') }}"></script>
<script src="{{ asset('js/angularjs/controllers/physicians.controller.js') }}"></script>
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
<link href="{{ asset('css/loading-bar.min.css') }}" rel="stylesheet">
<div class="container pb-3" ng-app="mro" ng-controller="physiciansCtrl" ng-init="getPatients()">
    {{-- @{{patients}} --}}
    <div class="row justify-content-center">
        <div class="card-group col-8 my-3 mobileHide">
            <div class="card">
                <div class="card-header text-white text-center bg-primary"><h3>ONQUEUE</h3></div>
                <div class="card-body text-center">
                    <h2>@{{onqueue}}</h2>
                </div>
            </div>
            <div class="card">
                <div class="card-header text-white text-center bg-primary"><h3>COMPLETED</h3></div>
                <div class="card-body text-center">
                    <h2>@{{completed}}</h2>
                </div>
            </div>
        </div>

        <!--MOBILE VIEW-->
        <div class="mobileShow py-3">
            <div class="btn-group">
                <button type="button" class="btn btn-warning btn-lg">
                    <a class="dashboard-panel font-weight-bold">ONQUEUE</a><span class="badge">@{{onqueue}}</span>
                    <span class="sr-only"></span>
                </button>
                <button type="button" class="btn btn-success btn-lg">
                    <a class="dashboard-panel font-weight-bold">COMPLETED</a><span class="badge">@{{completed}}</span>
                    <span class="sr-only"></span>
                </button>
            </div>
        </div>
        <!--END OF MOBILE VIEW-->
    </div>
    
    <div class="col mb-3">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text" id="inputGroupPrepend"><i class="fa fa-search"></i></span>
            </div>
            <input type="text" ng-model="patient_name" ng-keypress="submit($event)" class="form-control" placeholder="Search patient name" title="Type in a name" aria-describedby="inputGroupPrepend" required>
        </div>
    </div>

    <div class="border bt-1 mb-3"></div>
    <!-- TAB PANE MENU -->
    <ul class="nav nav-tabs" id="myTabJust" role="tablist">
        <li ng-click="filter_status=null;getPatients()" class="nav-item active">
            <a class="nav-link active" id="onqueue-tab" data-toggle="tab" href="#onqueue" role="tab" aria-controls="onqueue" aria-selected="true"><span class="fa fa-bed"></span> <span class="d-none d-sm-inline">  ON QUEUE </span></a>
        </li>
        <li ng-click="filter_status=0;getPatients()" class="nav-item">
            <a class="nav-link" id="posting-tab" data-toggle="tab" href="#posting" role="tab" aria-controls="posting" aria-selected="false"><span class="fa fa-retweet"></span> <span class="d-none d-sm-inline">FOR POSTING  </span></a>
        </li>
        <li ng-click="filter_status=1;getPatients()" class="nav-item">
            <a class="nav-link" id="completed-tab" data-toggle="tab" href="#completed" role="tab" aria-controls="completed" aria-selected="false"><span class="fa fa-check"></span> <span class="d-none d-sm-inline"> COMPLETED  </span></a>
        </li>
    </ul>
    
    <!--TAB PANE-->
    <div class="tab-content" id="myTabContentJust">
        <!-- TAB PANE CONTENT ADMITTED-->
        <div class="tab-pane fade show active" id="onqueue" role="tabpanel" aria-labelledby="onqueue-tab">
            <h4 class="d-block d-md-none">  ON QUEUE </h4>
            <div class="border bt-1"></div>
            <div class="divtable accordion-xs">
            <div class="tr headings" style="color: white;background-color: steelblue;">
                <div class="th reg" ng-click="sort('visit_number')">
                ADMISSION NO. 
                <span class="fa fa-sort" ng-show="sortKey=='visit_number'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th patid" ng-click="sort('patient_id')">
                PATIENT ID
                <span class="fa fa-sort" ng-show="sortKey=='patient_id'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th name" ng-click="sort('px_last_name')">
                NAME
                <span class="fa fa-sort" ng-show="sortKey=='px_last_name'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th gender" ng-click="sort('px_sex')">
                GENDER
                <span class="fa fa-sort" ng-show="sortKey=='px_sex'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th bdate" ng-click="sort('px_birthdate')">
                AGE
                <span class="fa fa-sort" ng-show="sortKey=='px_birthdate'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th pf" ng-click="sort('pf_amount')" align="left">
                PF AMOUNT
                <span class="fa fa-sort" ng-show="sortKey=='pf_amount'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <!-- <div class="th phic" >PHIC</div>
                <div class="th disc" >DISCOUNT</div>
                <div class="th total" >TOTAL</div> -->
                <div class="th action" align="left">ACTION</div>
            </div>
            <div ng-if="!patients_active.length" style="text-align: center">No record found</div>
            <div class="tr" ng-repeat="px in patients_active = (patients | orderBy:sortKey:reverse)">
                <div class="td reg accordion-xs-toggle" align="left">@{{px.visit_number}} <span class="d-inline d-md-none"> @{{px.px_last_name}}, @{{px.px_first_name}} @{{px.px_middle_name}}</span></div>
                <div class="accordion-xs-collapse">
                <div class="inner">   
                    <div class="td patid" align="left">@{{px.patient_id}}</div>
                    <div class="td name" align="left">@{{px.px_last_name}}, @{{px.px_first_name}} @{{px.px_middle_name}}</div>
                    <div class="td gender" align="left">@{{px.px_sex}}</div>
                    <div class="td bdate" align="left"> @{{calculateAge(px.px_birthdate)}}</div>
                    <div class="td pf" align="left">@{{px.pf_amount | number:2}}</div>
                    <!-- <div class="td phic" align="left">@{{px.phic_amount | number:2}}</div>
                    <div class="td disc" align="left">(@{{px.discount | number:2}})</div>
                    <div class="td total" align="left">@{{px.total | number:2}}</div> -->
                    <div class="td action" align="left"><a href="/physician/view_transaction/@{{px.external_id}}/@{{px.patient_id}}/@{{px.practitioner_id}}" >  <span class="fa fa-edit"></span> EDIT</a></div>
                </div>
                </div>
            </div>
            </div>
            <div ng-if="patients_active.length">
            <patients-pagination></patients-pagination>
            </div>
        </div>
        <!-- TAB PANE CONTENT POSTING-->
        <div class="tab-pane fade" id="posting" role="tabpanel" aria-labelledby="posting-tab">
            <h4 class="d-block d-md-none">  FOR POSTING </h4>
            <div class="border bt-1"></div>
            <div class="divtable accordion-xs">
            <div class="tr headings" style="color: white;background-color: steelblue;">
                <div class="th reg" ng-click="sort('visit_number')">
                ADMISSION NO. 
                <span class="fa fa-sort" ng-show="sortKey=='visit_number'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th patid" ng-click="sort('patient_id')">
                PATIENT ID
                <span class="fa fa-sort" ng-show="sortKey=='patient_id'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th name" ng-click="sort('px_last_name')">
                NAME
                <span class="fa fa-sort" ng-show="sortKey=='px_last_name'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th gender" ng-click="sort('px_sex')">
                GENDER
                <span class="fa fa-sort" ng-show="sortKey=='px_sex'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th bdate" ng-click="sort('px_birthdate')">
                AGE
                <span class="fa fa-sort" ng-show="sortKey=='px_birthdate'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th pf" ng-click="sort('pf_amount')" align="left">
                PF AMOUNT
                <span class="fa fa-sort" ng-show="sortKey=='pf_amount'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <!-- <div class="th phic" >PHIC</div>
                <div class="th disc" >DISCOUNT</div>
                <div class="th total" >TOTAL</div> -->
                <div class="th action" align="left">ACTION</div>
            </div>
            <div ng-if="!patients_posting.length" style="text-align: center">No record found</div>
            <div class="tr" ng-repeat="px in patients_posting = (patients | orderBy:sortKey:reverse)">
                <div class="td reg accordion-xs-toggle" align="left">@{{px.visit_number}} <span class="d-inline d-md-none"> @{{px.px_last_name}}, @{{px.px_first_name}} @{{px.px_middle_name}}</span></div>
                <div class="accordion-xs-collapse">
                <div class="inner">   
                    <div class="td patid" align="left">@{{px.patient_id}}</div>
                    <div class="td name" align="left">@{{px.px_last_name}}, @{{px.px_first_name}} @{{px.px_middle_name}}</div>
                    <div class="td gender" align="left">@{{px.px_sex}}</div>
                    <div class="td bdate" align="left"> @{{calculateAge(px.px_birthdate)}}</div>
                    <div class="td pf" align="left">@{{px.pf_amount | number:2}}</div>
                    <!-- <div class="td phic" align="left">@{{px.phic_amount | number:2}}</div>
                    <div class="td disc" align="left">(@{{px.discount | number:2}})</div>
                    <div class="td total" align="left">@{{px.total | number:2}}</div> -->
                    <div class="td action" align="left"><a href="/physician/view_transaction/@{{px.external_id}}/@{{px.patient_id}}/@{{px.practitioner_id}}" >  <span class="fa fa-edit"></span> VIEW</a></div>
                </div>
                </div>
            </div>
            </div>
            <div ng-if="patients_posting.length">
            <patients-pagination></patients-pagination>
            </div>
        </div>  
        <!-- TAB PANE CONTENT onqueue-->
        <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completed-tab">
            <h4 class="d-block d-md-none">  POSTED </h4>
            <div class="border bt-1"></div>
            <div class="divtable accordion-xs">
            <div class="tr headings" style="color: white;background-color: steelblue;">
                <div class="th reg" ng-click="sort('visit_number')">
                ADMISSION NO. 
                <span class="fa fa-sort" ng-show="sortKey=='visit_number'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th patid" ng-click="sort('patient_id')">
                PATIENT ID
                <span class="fa fa-sort" ng-show="sortKey=='patient_id'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th name" ng-click="sort('px_last_name')">
                NAME
                <span class="fa fa-sort" ng-show="sortKey=='px_last_name'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th gender" ng-click="sort('px_sex')">
                GENDER
                <span class="fa fa-sort" ng-show="sortKey=='px_sex'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th bdate" ng-click="sort('px_birthdate')">
                AGE
                <span class="fa fa-sort" ng-show="sortKey=='px_birthdate'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <div class="th pf" ng-click="sort('pf_amount')" align="left">
                PF AMOUNT
                <span class="fa fa-sort" ng-show="sortKey=='pf_amount'" ng-class="{'fa fa-menu-up':reverse,'fa fa-menu-down':!reverse}"></span>
                </div>
                <!-- <div class="th phic" >PHIC</div>
                <div class="th disc" >DISCOUNT</div>
                <div class="th total" >TOTAL</div> -->
                <div class="th action" align="left">ACTION</div>
            </div>
            <div ng-if="!patients_completed.length" style="text-align: center">No record found</div>
            <div class="tr" ng-repeat="px in patients_completed = (patients | orderBy:sortKey:reverse)">
                <div class="td reg accordion-xs-toggle" align="left">@{{px.visit_number}} <span class="d-inline d-md-none"> @{{px.px_last_name}}, @{{px.px_first_name}} @{{px.px_middle_name}}</span></div>
                <div class="accordion-xs-collapse">
                <div class="inner">   
                    <div class="td patid" align="left">@{{px.patient_id}}</div>
                    <div class="td name" align="left">@{{px.px_last_name}}, @{{px.px_first_name}} @{{px.px_middle_name}}</div>
                    <div class="td gender" align="left">@{{px.px_sex}}</div>
                    <div class="td bdate" align="left"> @{{calculateAge(px.px_birthdate)}}</div>
                    <div class="td pf" align="left">@{{px.pf_amount | number:2}}</div>
                    <!-- <div class="td phic" align="left">@{{px.phic_amount | number:2}}</div>
                    <div class="td disc" align="left">(@{{px.discount | number:2}})</div>
                    <div class="td total" align="left">@{{px.total | number:2}}</div> -->
                    <div class="td action" align="left"><a href="/physician/view_transaction/@{{px.external_id}}/@{{px.patient_id}}/@{{px.practitioner_id}}" >  <span class="fa fa-edit"></span> VIEW</a></div>
                </div>
                </div>
            </div>
            <!-- <div class="tr" ng-if="patients_completed.length" >
                <div class="td reg accordion-xs-toggle" align="left">GRAND TOTAL</div>
                <div class="accordion-xs-collapse">
                <div class="inner">   
                    <div class="td patid" align="left"></div>
                    <div class="td name" align="left"></div>
                    <div class="td gender" align="left"></div>
                    <div class="td bdate" align="left"></div>
                    <div class="td pf" align="left">@{{grand_total_pf_amount | number:2}}</div>
                    <div class="td phic" align="left">@{{grand_total_phic_amount | number:2}}</div>
                    <div class="td disc" align="left">(@{{grand_total_discount | number:2}})</div>
                    <div class="td total" align="left">@{{grand_total_subtotal | number:2}}</div>
                    <div class="td action" align="left"></div>
                </div>
                </div>
            </div> -->
            </div>
            <div ng-if="patients_completed.length">
            <patients-pagination></patients-pagination>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(".dashboard-panel").click(function(){
        tabselected = $(this).text().toLowerCase();
        tabselected = tabselected.replace(/\s/g, '');
        $('#myTabJust a[href="#' + tabselected + '"]').tab('show').trigger('click');
    });
</script>
@endsection
