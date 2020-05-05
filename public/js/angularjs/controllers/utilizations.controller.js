  angular.
  module('mro').
  controller('auditLogsCtrl', auditLogsCtrl);
  function auditLogsCtrl($http, $scope, $filter){
    $scope.utilization = [];
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.range = [];

    $scope.sort = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = !$scope.reverse; //if true make it false and vice versa
    }

    $scope.getUtilization = function(pageNumber){

      if(pageNumber===undefined){
        pageNumber = '1';
      }

      $http({
        method : "POST",
        url : '/audit_logs/getUtilization/'+pageNumber,
        data: { username: $scope.username , start_date: $scope.start_date, end_date: $scope.end_date }
      }).then(function mySuccess(response) {
        console.log(response.data.data);
        respo_data = response.data.data;
        if(response.data.error.status)
            alert(response.data.error.message)
        else{
          

          $scope.utilization        = respo_data.data;
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
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getUtilization(1)">«</a></li>'+
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getUtilization(currentPage-1)">‹ Prev</a></li>'+
          '<li ng-repeat="i in range" ng-class="{active : currentPage == i}">'+
              '<a href="javascript:void(0)" ng-click="getUtilization(i)">{{i}}</a>'+
          '</li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getUtilization(currentPage+1)">Next ›</a></li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getUtilization(totalPages)">»</a></li>'+
        '</ul>'
     };
  });