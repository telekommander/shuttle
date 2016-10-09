<?php

$app->group("", function () use ($app) {

    $controller = new App\Controller\HomeController($app);
    $login      = new App\Controller\LoginController($app);

    $app->get("/", $controller("dispatch"));
    $app->get("/dashboard", $controller("dashboard"));
    $app->get("/register", $controller("register"));

    $app->get("/login", $login("login"));
    $app->get("/logout", $login("logout"));

    $app->post("/login", $login("loginPost"));
    $app->post("/register", $login("registerPost"));

    $app->get("/testjson/[{option}]", $controller("testJson"));

});

$route = App\Model\Route::all();
foreach ($route as $rt)
{
	$app->get("/".$rt->route,$rt->address)->setName($rt->route);
}