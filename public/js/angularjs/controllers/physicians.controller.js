angular.
  module('mro').
  controller('physiciansCtrl', physiciansCtrl);

  function physiciansCtrl($http, $scope, $filter, $interval){
    $scope.patients = [];
    $scope.transaction_details = [];
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.range = [];
    $scope.filter_status = null;
    $scope.tab_selected = 'onqueue';
    $scope.patient_name = '';
    $scope.onqueue = 0;
    $scope.completed = 0;
    $scope.disable_edit_pf = false;
    
    // if(jQuery(window).width() >= 767){
    //   $interval(function() {
    //     console.log($scope.currentPage);
    //     $scope.getPatients();
    //   }, 20000);
    // }
    $scope.submit = function($event){
      var keyCode = $event.which || $event.keyCode;
      if (keyCode === 13) {
          $scope.getPatients();
      }
    }
    $scope.sort = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = !$scope.reverse; //if true make it false and vice versa
    }

    $scope.sortDesc = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = true; //if true make it false and vice versa
    }

    $scope.calculateAge = function(birthday) { // birthday is a date
      var birthday = new Date(birthday);
      var ageDifMs = Date.now() - birthday.getTime();
      var ageDate = new Date(ageDifMs); // miliseconds from epoch
      return Math.abs(ageDate.getUTCFullYear() - 1970);
    }

    $scope.calculateDaysAdmitted = function(admission_datetime, mgh_datetime) { 
      var admission_datetime = new Date(admission_datetime);
      var mgh_datetime = new Date(mgh_datetime);
      // To calculate the time difference of two dates 
      var Difference_In_Time = Math.abs((mgh_datetime.getTime()?mgh_datetime.getTime():Date.now()) - admission_datetime.getTime()); 
      // To calculate the no. of days between two dates 
      var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24); 
      return Math.ceil(Difference_In_Days);
    }

    $scope.getPatients = function(pageNumber){
      // console.log($scope.filter_status);
      if(pageNumber===undefined){
        pageNumber = '1';
      }
      // console.log(angular.element('meta[name="csrf-token"]').attr('content'));
      $http({
        headers: {
          'X-CSRF-TOKEN': angular.element('meta[name="csrf-token"]').attr('content')
        },
        method : "POST",
        url : '/physician/get_patients?page='+pageNumber,
        data: {  filter_status: $scope.filter_status, patient_name: $scope.patient_name }
      }).then(function mySuccess(response) {
        // console.log(response);

        respo_data = response.data.data;
        if(!response.data.success)
            alert(response.data.message)
        else{
    
          if(respo_data !== null){
            $scope.onqueue = response.data.onqueue;
            $scope.completed = response.data.completed;
            // var no_of_rows = respo_data.data.length;
            // $scope.grand_total_pf_amount = 0;
            // $scope.grand_total_phic_amount = 0;
            // $scope.grand_total_discount = 0;
            // $scope.grand_total_subtotal = 0;
            // angular.forEach(respo_data.data, function(pv_val, pv_key) {
            //   $scope.grand_total_pf_amount += parseInt(pv_val.pf_amount);
            //   $scope.grand_total_phic_amount += parseInt(pv_val.phic_amount);
            //   $scope.grand_total_discount += parseInt(pv_val.discount);
            //   $scope.grand_total_subtotal += parseInt(pv_val.pf_amount) + parseInt(pv_val.phic_amount) - parseInt(pv_val.discount);
            //   respo_data.data[pv_key].total = parseInt(pv_val.pf_amount) + parseInt(pv_val.phic_amount) - parseInt(pv_val.discount);
            // });

            $scope.patients        = respo_data.data;
            $scope.totalPages   = respo_data.last_page;
            $scope.currentPage  = respo_data.current_page;

            // Pagination Range
            var pages = [];

            for(var i=1;i<=respo_data.last_page;i++) {          
              pages.push(i);
            }

            $scope.range = pages;
          }else{
            $scope.patients=[];
            $scope.range = [];
          }
        }
      }, function myError(response) {
          alert(response.statusText);
      });
    };

    $scope.viewTransaction = function(external_id, patient_id, practitioner_id){
      $http({
        method : "GET",
        url : '/physician/get_transaction_details/'+external_id+'/'+patient_id+'/'+practitioner_id
      }).then(function mySuccess(response) {
        if(response.data.success){
          $scope.transaction_details = response.data.data;
          //Manipulation before display
          $scope.transaction_details['PatientVisit']['age'] = $scope.calculateAge($scope.transaction_details['PatientVisit']['px_birthdate']);
          $scope.transaction_details['PatientVisit']['no_days_admitted'] = $scope.calculateDaysAdmitted($scope.transaction_details['PatientVisit']['admission_datetime'], $scope.transaction_details['PatientVisit']['mgh_datetime']);
        
          if($scope.transaction_details['PatientVisit']['patient_visit_status'] == 'X' || $scope.transaction_details['PatientVisit']['status'] !== null)
            $scope.disable_edit_pf = true;
          if(!$scope.disable_edit_pf){
            if($scope.transaction_details['PatientVisit']['expiration_datetime']){
              var x = setInterval(function() {
                $http({
                  headers: {
                    'X-CSRF-TOKEN': angular.element('meta[name="csrf-token"]').attr('content')
                  },
                  method : "post",
                  url : '/physician/get_remaining_time/',
                  data: {expiration_datetime: $scope.transaction_details['PatientVisit']['expiration_datetime']},
                  ignoreLoadingBar: true
                }).then(function mySuccess(response) {
                    // console.log(response.data.abs);
                    // console.log(response.data.readable);
                    if (response.data.abs < 0) {
                      clearInterval(x);
                      // $scope.disable_edit_pf = true;
                      angular.element('#professionalFee :submit').prop('disabled',true);
                      angular.element('#professionalFee :input').prop('readonly','readonly');
                    }else{
                      angular.element("#pf_timer").removeClass('d-none').text('Remaining time to input your PF: '+response.data.readable);
                    }
                  }, function myError(response) {
                    alert(response.statusText);
                }); 
              }, 1000);
            }
          }else{
            angular.element('#professionalFee :submit').prop('disabled',true);
            angular.element('#professionalFee :input').prop('readonly','readonly');
          }
          //PF Display Toggle
          if($scope.transaction_details['PatientVisit']['show_pf'])
            angular.element('#toggle-pf-display').bootstrapToggle('on');
          else
            angular.element('#toggle-pf-display').bootstrapToggle('off');

        }else
          alert(response.data.message);
      }, function myError(response) {
          alert(response.statusText);
      });
    };
    
    $scope.toggleDisplayPf = function(){
      if(angular.element('#toggle-pf-display').prop('checked'))
        show_toggle_val = 0;
      else
        show_toggle_val = 1;

      $http({
        method : "GET",
        url:'/physician/toggle_display_pf/'+angular.element("[name='data[PatientCareProvider][id]']").val()+'/'+show_toggle_val,
      }).then(function mySuccess(response) {
        console.log(response.data.message);
      }, function myError(response) {
          alert(response.statusText);
      });
    };
  };
  angular.
  module('mro')
  .directive('patientsPagination', function(){  
     return{
        restrict: 'E',
        template: '<nav aria-label="Patient list pagination"><ul class="pagination">'+
          '<li class="page-item" ng-show="currentPage != 1"><a class="page-link" href="javascript:void(0)" ng-click="getPatients(1)">«</a></li>'+
          '<li class="page-item" ng-show="currentPage != 1"><a class="page-link" href="javascript:void(0)" ng-click="getPatients(currentPage-1)">‹ Prev</a></li>'+
          '<li class="page-item" ng-repeat="i in range" ng-class="{active : currentPage == i}">'+
              '<a class="page-link" href="javascript:void(0)" ng-click="getPatients(i)">{{i}}</a>'+
          '</li>'+
          '<li class="page-item" ng-show="currentPage != totalPages"><a class="page-link" href="javascript:void(0)" ng-click="getPatients(currentPage+1)">Next ›</a></li>'+
          '<li class="page-item" ng-show="currentPage != totalPages"><a class="page-link" href="javascript:void(0)" ng-click="getPatients(totalPages)">»</a></li>'+
        '</ul></nav>'
     };
  });
  angular.
  module('mro')
  .directive('edit-patient', function () {
    return {
        link: function (scope, element, attrs) {
            element.bind('edit-patient', function () {
                alert('Clicked on ID : '+ attrs.id);
            });
        },
    };
  });

