angular.
  module('mro').
  controller('usersCtrl', usersCtrl);

  function usersCtrl($http, $scope, $filter){
    $scope.users = [];
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.range = [];

    $scope.sort = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = !$scope.reverse; //if true make it false and vice versa
    }

    $scope.getUsers = function(pageNumber){

      if(pageNumber===undefined){
        pageNumber = '1';
      }

      $http({
        method : "POST",
        url : '/users/getUsers/'+pageNumber,
        data: { username: $scope.username , name: $scope.name, role: $scope.role }
      }).then(function mySuccess(response) {
        // console.log(response.data.data.data);
        respo_data = response.data.data;
        if(response.data.error.status)
            alert(response.data.error.message)
        else{
          

          $scope.users        = respo_data.data;
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
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getUsers(1)">«</a></li>'+
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getUsers(currentPage-1)">‹ Prev</a></li>'+
          '<li ng-repeat="i in range" ng-class="{active : currentPage == i}">'+
              '<a href="javascript:void(0)" ng-click="getUsers(i)">{{i}}</a>'+
          '</li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getUsers(currentPage+1)">Next ›</a></li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getUsers(totalPages)">»</a></li>'+
        '</ul>'
     };
  });