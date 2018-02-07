<?php
/**
 * Created by PhpStorm.
 * User: Simone
 * Date: 17/01/2018
 * Time: 12.38
 */

?>



<div class="container">

    <div class="loginform">
    <form class="form-signin" ng-submit="submit(loginform)" name="loginform">
        <h2 class="form-signin-heading">Accesso</h2>
        <label for="login" class="sr-only">Email</label>
        <input type="email" id="login" class="form-control" placeholder="Email" required ng-model="formData.inputEmail" autofocus value="cubico.it@gmail.com ">
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" ng-model="formData.inputPassword" required>
        <div class="checkbox">
            <label>
                <input type="checkbox" value="remember-me" name="remember-me"> Ricordami
            </label>
        </div>
        <div class="formerror" ng-show="error">{{error}}</div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>

    </div>
</div> <!-- /container -->