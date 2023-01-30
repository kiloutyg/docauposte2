/**
 * Created by Michael on 27/03/14.
 */
angular.module('angularUtils.filters.startsWith', [])

    .filter('startsWith', function() {
        return function(array, search) {
var searchString = search.toString();

            var matches = [];
            for(var i = 0; i < array.length; i++) {
              console.log(array[i].num.indexOf(searchString));
                if (array[i].num.indexOf(searchString) === 0 &&
                    searchString.length <= array[i].num.length) {
                    matches.push(array[i]);
                }
            }
            return matches;
        };
    });

var app = angular.module('app', ['angularUtils.filters.startsWith']);

app.config( [
    '$compileProvider',
    function( $compileProvider )
    {
        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|javascript):/);
        // Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
    }
]);

app.controller('search_Ctrl', ['$scope', function($scope) {

    $scope.tap = function(c) {
        $scope.filtre.num += c;

    };
    $scope.back = function() {
        $scope.filtre.num = $scope.filtre.num.substring(0, $scope.filtre.num.length - 1)
    };
    $scope.raz = function() {
        $scope.filtre.num = ''
    };
}]);

app.controller('search_nomenclature_Ctrl', ['$scope', function($scope) {
$scope.idSAP='';
    $scope.tap = function(c) {
        $scope.idSAP += c;
    };
    $scope.back = function() {
        $scope.idSAP = $scope.idSAP.substring(0, $scope.idSAP.length - 1)
    };
    $scope.raz = function() {
        $scope.idSAP = ''
    };
}]);

app.controller('liste_Ctrl', ['$scope', function($scope) {}]);
