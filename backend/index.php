<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>PICKMEET BACKEND</title>

    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="css/style.css" media="all"/>

    <script src="js/angular.js"></script>
    <script src="js/angular-route.js"></script>
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.js"></script>

    <!-- ionic/angularjs js -->
    <script src="../pickmeet/www/lib/ionic/js/ionic.bundle.js"></script>

    <script src="../pickmeet/www/js/services.js"></script>
    <script src="js/config.js"></script>
    <script src="js/controllers.js"></script>
    <script src="js/app.js"></script>

</head>
<body ng-app="backendApp">



<main role="main" class="container">
    <h1>Pickmeet Backend</h1>

    <div ng-view></div>

    <? include ("templates/footer.php"); ?>

</main><!-- /.container -->



</body>
</html>