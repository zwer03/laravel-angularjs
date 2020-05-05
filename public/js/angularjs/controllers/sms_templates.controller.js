  angular.
  module('mro').
  controller('SmsTemplatesCtrl', SmsTemplatesCtrl);
  function SmsTemplatesCtrl($http, $scope, $filter){
    $scope.sms_templates = [];
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.range = [];

    $scope.sort = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = !$scope.reverse; //if true make it false and vice versa
    }

    $scope.getSmsTemplates = function(pageNumber){

      if(pageNumber===undefined){
        pageNumber = '1';
      }

      $http({
        method : "POST",
        url : '/sms_templates/getSmsTemplates/'+pageNumber,
        data: { username: $scope.username , start_date: $scope.start_date, end_date: $scope.end_date }
      }).then(function mySuccess(response) {
        console.log(response.data.data.data);
        respo_data = response.data.data;
        if(response.data.error.status)
            alert(response.data.error.message)
        else{
          

          $scope.sms_templates        = respo_data.data;
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
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getSmsTemplates(1)">«</a></li>'+
          '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="getSmsTemplates(currentPage-1)">‹ Prev</a></li>'+
          '<li ng-repeat="i in range" ng-class="{active : currentPage == i}">'+
              '<a href="javascript:void(0)" ng-click="getSmsTemplates(i)">{{i}}</a>'+
          '</li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getSmsTemplates(currentPage+1)">Next ›</a></li>'+
          '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="getSmsTemplates(totalPages)">»</a></li>'+
        '</ul>'
     };
  });