  angular.
  module('mro').
  controller('ReportsCtrl', ReportsCtrl);
  function ReportsCtrl($http, $scope, $filter){
    $scope.patients = [];
    $scope.sort = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = !$scope.reverse; //if true make it false and vice versa
    }

    $scope.getIncome = function(pageNumber){

      $http({
        method : "POST",
        url : '/reports/getIncome/',
        data: { username: $scope.username , start_date: $scope.start_date, end_date: $scope.end_date }
      }).then(function mySuccess(response) {
        console.log(response.data.data.data);
        respo_data = response.data.data;
        if(response.data.error.status)
            alert(response.data.error.message)
        else{
          $scope.patients        = respo_data.data;
        }
      }, function myError(response) {
          alert(response.statusText);
      });
    };
  };
