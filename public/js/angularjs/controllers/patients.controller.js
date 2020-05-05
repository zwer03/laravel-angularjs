angular.
  module('mro').
  controller('patientsCtrl', patientsCtrl);
  
  function patientsCtrl($http, $scope, $filter, $anchorScroll, $location, anchorService, getRecordService){
    $scope.patient_orders = [];
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.range = [];
    $scope.counter = 0;
    $scope.pdfDivSize = 'col-xs-12';
    $scope.specimen_ids = [];

    $scope.sort = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = !$scope.reverse; //if true make it false and vice versa
    }

    $scope.clearPDf = function(){
      $scope.specimen_ids = [];
      $scope.counter = 0;
      angular.element( document ).find('#pdfHolder').empty();
      // console.log('here');
    }
    $scope.deletePdf = function(){
      $scope.counter = 0;
      angular.element( document ).find('#pdfHolder').parent();
      console.log('here');
    }
    $scope.getPdf = function(specimen_id) {
      
      console.log($scope.specimen_ids.indexOf( specimen_id));
      console.log($scope.specimen_ids);
      if($scope.specimen_ids.indexOf(specimen_id) == -1 && $scope.specimen_ids.indexOf(specimen_id)){
        if(!$scope.counter == 0){
          angular.element( document ).find('.pdfDiv').removeClass('col-xs-12').addClass("col-xs-6");
          $scope.pdfDivSize = 'col-xs-6';
        }else
          $scope.pdfDivSize = 'col-xs-12';
          // $scope.toggle=!$scope.toggle;
          // Use this if pdf file is from test result pdf_file column
          // //Filter patient_orders by specimen id 
          // selected_tr = $filter('filter')($scope.patient_orders, {'specimen_id':specimen_id});
          // // Show each test result
          // angular.forEach(selected_tr[0].TestOrder.TestResult, function(tr_val, tr_key) {
          //   // console.log(tr_val.pdf_file);
          //   var pdfHolder = angular.element( document ).find('#pdfHolder').empty();
          //   pdfHolder.append(
          //     '<object data='+$scope.pdfUrl+' type="application/pdf" width="100%" height="800px" >'+ 
          //       '<p>It appears you dont have a PDF plugin for this browser.'+
          //        'No biggie... you can <a href="resume.pdf">click here to'+
          //       'download the PDF file.</a></p>'+  
          //     '</object>'
          //   ); 
          // });

          $scope.pdfUrl='/patients/getPdf/'+specimen_id;
          var pdfHolder = angular.element( document ).find('#pdfHolder');
          pdfHolder.append(
            '<div class="'+$scope.pdfDivSize+' anchor pdfDiv" id="anchorpdfDiv-'+specimen_id+'"><object data='+$scope.pdfUrl+' type="application/pdf" width="100%" height="800px" >'+ 
              '<p>It appears you dont have a PDF plugin for this browser.'+
               'No biggie... you can <a href="'+$scope.pdfUrl+'">click here to'+
              'download the PDF file.</a></p>'+  
            '</object></div>'
          ); 
          $scope.counter++;
          $scope.specimen_ids.push(specimen_id);
      }
      anchorService.gotoAnchor("pdfDiv-"+specimen_id);
    };
    $scope.getPatientOrders = function(pageNumber){
      // var url = '/patients/getPatientOrders/';
      // var data = { start_date: $scope.start_date , end_date: $scope.end_date, first_name: $scope.firstname, last_name: $scope.lastname, patient_id: $scope.patient_id};
      // getRecordService.list(pageNumber, url, data);
      // console.log(getRecordService.list());
      // $scope.totalPages   = respo_data.last_page;
      // $scope.currentPage  = respo_data.current_page;

      // // Pagination Range
      // var pages = [];

      // for(var i=1;i<=respo_data.last_page;i++) {          
      //   pages.push(i);
      // }

      // $scope.range = pages;
      if(pageNumber===undefined){
        pageNumber = '1';
      }

      $http({
        method : "POST",
        url : '/patients/getPatientOrders/'+pageNumber,
        data: { start_date: $scope.start_date , end_date: $scope.end_date, first_name: $scope.firstname, last_name: $scope.lastname, patient_id: $scope.patient_id}
      }).then(function mySuccess(response) {
        // console.log(response.data.data.data);
        respo_data = response.data.data;
        if(response.data.error.status)
            alert(response.data.error.message)
        else{
          if(respo_data !== null){
            // Combine all test result examination
            angular.forEach(respo_data.data, function(patient_order_val, patient_order_key) {
              respo_data.data[patient_order_key].TestOrder.examinations = "";
              angular.forEach(patient_order_val.TestOrder.TestResult, function(tr_val, tr_key) {
                respo_data.data[patient_order_key].TestOrder.examinations += tr_val.TestGroup.name;
              });
            });

            $scope.patient_orders        = respo_data.data;
            $scope.totalPages   = respo_data.last_page;
            $scope.currentPage  = respo_data.current_page;

            // Pagination Range
            var pages = [];

            for(var i=1;i<=respo_data.last_page;i++) {          
              pages.push(i);
            }

            $scope.range = pages;
          }else{
            $scope.patient_orders=[];
            $scope.range = [];
          }
        }
      }, function myError(response) {
          alert(response.statusText);
      });
    };
  };

  angular.
  module('mro').
  directive('patientOrdersPagination', function(){  
     return{
        restrict: 'E',
        template: '<ul class="pagination">'+
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getPatientOrders(1)">«</a></li>'+
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getPatientOrders(currentPage-1)">‹ Prev</a></li>'+
          '<li ng-repeat="i in range" ng-class="{active : currentPage == i}">'+
              '<a href="javascript:void(0)" ng-click="getPatientOrders(i)">{{i}}</a>'+
          '</li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getPatientOrders(currentPage+1)">Next ›</a></li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getPatientOrders(totalPages)">»</a></li>'+
        '</ul>'
     };
  });