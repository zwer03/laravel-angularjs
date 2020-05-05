//= wrapped

angular
    .module("mro")
    .factory("anchorService", anchorService);

function anchorService($anchorScroll, $location) {
	var vm = this;
    
    return {
    	gotoAnchor: function(x) {
            var newHash = 'anchor' + x;
            if ($location.hash() !== newHash) {
              // set the $location.hash to `newHash` and
              // $anchorScroll will automatically scroll to it
              $location.hash('anchor' + x);
            } else {
              // call $anchorScroll() explicitly,
              // since $location.hash hasn't changed
              $anchorScroll();
            }
        }
    };
}