var standenApp = angular.module('standenApp', []);

standenApp.controller('MainCtrl', function ($scope, $http) {
    $scope.fetchStanden = function () {
        $scope.loader = true;
        $http.get("biljartpoint.php?query=" + $scope.query).then(function (response) {
            $scope.loader = false;
            $scope.standen = response.data;
        });
    };
    $scope.contains = function(json, value) {
        var json_str = JSON.stringify(json).toLowerCase();
        return json_str.search(value.toLowerCase()) > -1;
    };
});
