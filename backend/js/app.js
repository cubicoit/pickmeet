/**
 * Created by Simone on 17/01/2018.
 */

var backendApp = angular.module('backendApp', ['ngRoute', 'backendApp.controllers', 'pickmeetapp.services']);



function backendAppRouteConfig($routeProvider) {
    $routeProvider.

        when('/login', {
            controller: 'loginController' ,
            templateUrl: 'templates/login.php'
        }).

        when('/home', {
            controller: 'homeController' ,
            templateUrl: 'templates/home.php'
        }).



        otherwise({
            redirectTo: '/login'
        });



    //$rootScope.config = config;
};

// Set up our route so the AMail service can find it
backendApp.config(backendAppRouteConfig);

backendApp.run(function($rootScope) {
    $rootScope.config = config;
});

/*Jquery*/

$(document).ready(function() {


    console.log("document ready");
});