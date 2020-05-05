  angular.
  module('mro').
  controller('MedicalPackagesCtrl', MedicalPackagesCtrl);
  function MedicalPackagesCtrl($http, $scope, $filter){
    $scope.medical_packages = [];
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.range = [];

    $scope.sort = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = !$scope.reverse; //if true make it false and vice versa
    }

    $scope.getMedicalPackages = function(pageNumber){

      if(pageNumber===undefined){
        pageNumber = '1';
      }

      $http({
        method : "POST",
        url : '/medical_packages/getMedicalPackages/'+pageNumber,
        data: { username: $scope.username , start_date: $scope.start_date, end_date: $scope.end_date }
      }).then(function mySuccess(response) {
        console.log(response.data.data.data);
        respo_data = response.data.data;
        if(response.data.error.status)
            alert(response.data.error.message)
        else{
          

          $scope.medical_packages        = respo_data.data;
          $scope.totalPages   = respo_data.last_page;
          $scope.currentPage  = respo_data.current_page;

          // Pagination Range
          var pages = [];

          for(var i=1;i<=respo_data.last_page;i++) {          
            pages.push(i);
          }

          $scope.range = pages;
        }
      }, function myError(response) {
          alert(response.statusText);
      });
    };
  };

  angular.
  module('mro')
  .directive('patientOrdersPagination', function(){  
     return{
        restrict: 'E',
        template: '<ul class="pagination">'+
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getMedicalPackages(1)">«</a></li>'+
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getMedicalPackages(currentPage-1)">‹ Prev</a></li>'+
          '<li ng-repeat="i in range" ng-class="{active : currentPage == i}">'+
              '<a href="javascript:void(0)" ng-click="getMedicalPackages(i)">{{i}}</a>'+
          '</li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getMedicalPackages(currentPage+1)">Next ›</a></li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getMedicalPackages(totalPages)">»</a></li>'+
        '</ul>'
     };
  });