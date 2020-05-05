angular.
  module('mro').
  controller('physicianViewTx', physicianViewTx);
  
  function physicianViewTx($http, $scope, $filter, $location){
    $scope.pf_amount1 = 123213;
    $scope.here = function($event){
      console.log($scope.pf_amount1);
    }
  };

