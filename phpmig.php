<?php

date_default_timezone_set("Europe/Berlin");

require_once "vendor/autoload.php";

use Phpmig\Adapter;
use Illuminate\Database\Capsule\Manager as Capsule;

$getconfig  = file_get_contents(__DIR__ . "/config/config.local.json");
$config     = json_decode($getconfig, TRUE);
$settings   = $config["app"];
$capsule    = new Capsule;
$capsule->addConnection($settings["settings"]["database"]);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container = new ArrayObject();
$container['phpmig.adapter'] = new Adapter\Illuminate\Database($capsule, 'migrations');
$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';
$container['phpmig.migrations_template_path'] = $container['phpmig.migrations_path'] . DIRECTORY_SEPARATOR . '.template.php';

return $container;