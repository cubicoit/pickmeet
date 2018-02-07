/**
 * Created by Simone on 17/01/2018.
 */

console.log("controllers.js");


angular.module('backendApp.controllers', [])



    .controller('loginController', [ '$scope',  '$http',  'myservice',  'UserServices', '$rootScope',

                function( $scope,  $http,  myservice,  UserServices, $rootScope) {
        console.log("loginController");
        $scope.showLogin = false;

        $scope.submitted = false;

        $scope.error = false;

        $scope.submit = function(loginform) {
            console.log("submit form1! " + $rootScope.config.URL_API);
            $scope.submitted = true;
            $scope.submitButtonDisabled = true;

            /*
            $http({
                method  : 'POST',
                //url     : 'libs/sendmail.php',
                url     : $rootScope.config.URL_API,
                data    : $.param($scope.formData),  //param method from jQuery
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  //set the headers so angular passing info as form data (not request payload)
            }).success(function(data){
                console.log(data);
                if (data.success) { //success comes from the return json object
                    $scope.submitButtonDisabled = true;
                    $scope.resultMessage = data.message;
                    $scope.result='bg-success';
                } else {
                    $scope.submitButtonDisabled = false;
                    $scope.resultMessage = data.message;
                    $scope.result='bg-danger';
                }
            });*/


            console.log("submit form2! -" + $scope.formData.inputEmail);

            var postData = {
                login: $scope.formData.inputEmail,
                password: $scope.formData.inputPassword,
                frutto: "banana"
            }

            $http.post(myservice.restpath + 'login', postData
            ).then(function successCallback(response) {
                    // this callback will be called asynchronously
                    // when the response is available
                    var data = response.data;
                    // Do something successful
                    console.log("post inviato correttamente");

                    if (data.error) {
                        switch (data.error) {
                            case 'User not found or wrong login':
                                $scope.error = 'Dati di accesso non validi, riprovare. ' + data.error;
                                $scope.formData.inputPassword = '';
                                break;
                            default:
                                $scope.error = 'Si è verificato un errore durante il login. ' + data.error;

                                break;
                        }
                    } else {
                        UserServices.setToken(data.result.token);
                        UserServices.user = data.result.user;

                        $scope.error = "Login corretto";
                        //$state.go('home');
                        window.location.href='#!/home'
                    }
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.

                    console.log("errore nell'invio via POST");
                });




        }

    }])

.controller('homeController', [ '$scope', '$http',  'myservice',  'UserServices', '$rootScope',

    function( $scope, $http,  myservice,  UserServices, $rootScope) {
        console.log("homeController");
    }])
/*
function loginController($scope) {

    console.log("loginController");
    $scope.hello = "hello banana";
}*/