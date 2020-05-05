//= wrapped

angular
    .module("mro")
    .factory("getRecordService", getRecordService);

function getRecordService($http, $rootScope) {
	  var vm = this;
    return {
    	list: function(pageNumber, url, data) {
        if(pageNumber===undefined){
          pageNumber = '1';
        }
  
        $http({
          method : "POST",
          url : url+pageNumber,
          data: data
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
  
              vm.patient_orders        = respo_data.data;
              vm.totalPages   = respo_data.last_page;
              vm.currentPage  = respo_data.current_page;
  
              // Pagination Range
              var pages = [];
  
              for(var i=1;i<=respo_data.last_page;i++) {          
                pages.push(i);
              }
  
              vm.range = pages;
            }else{
              vm.patient_orders=[];
              vm.range = [];
            }
          }
        }, function myError(response) {
            alert(response.statusText);
        });
      }
    };
}