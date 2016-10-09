<?php

use Slim\App as App;

// FORCE ERROR REPORTING
// SOME SERVERS ARE RUNNING IN QUIET MODE
error_reporting(E_ALL);
ini_set( "display_errors","1");

// TO HELP THE BUILT-IN PHP DEV SERVER, CHECK IF THE REQUEST WAS ACTUALLY FOR
// SOMETHING WHICH SHOULD PROBABLY BE SERVED AS A STATIC FILE
if (PHP_SAPI === "cli-server" && $_SERVER["SCRIPT_FILENAME"] !== __FILE__) {
    return false;
}

// COMPOSER AUTOLOADER
require __DIR__ . "/../vendor/autoload.php";

session_start();

// CHECK IF WE ARE ON LOCALHOST
$ip   = ["127.0.0.1", "::1"];
$host = (in_array($_SERVER["REMOTE_ADDR"], $ip)) ? "local" : "production";

// GET CONFIG FILE
$getconfig  = file_get_contents(__DIR__ . "/../config/config." . $host . ".json");
$config     = json_decode($getconfig, TRUE);

// INSTANTIATE APPLICATION
$settings   = $config["app"];
$app        = new App($settings);

// GET CORE FILES
foreach (glob( __DIR__ . "/../app/core/*.php") as $filename)
{
    require_once $filename;
}

// RUN!
$app->run();