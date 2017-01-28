<?php

$app->group("", function () use ($app) {

    $app->get("/", "App\\Controller\\HomeController:dispatch");
    $app->get("/dashboard", "App\\Controller\\HomeController:dashboard");
    $app->get("/register", "App\\Controller\\HomeController:register");
//
    $app->get("/login", "App\\Controller\\LoginController:login");
    $app->get("/logout",  "App\\Controller\\LoginController:logout");
//
    $app->post("/login", "App\\Controller\\LoginController:loginPost");
    $app->post("/register", "App\\Controller\\LoginController:registerPost");
//
    $app->get("/testjson/[{option}]", "App\\Controller\\HomeController:testJson");

});

$route = App\Model\Route::all();
foreach ($route as $rt)
{
	$app->get("/".$rt->route,$rt->address)->setName($rt->route);
}