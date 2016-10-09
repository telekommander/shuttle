<?php

$container = $app->getContainer();

// TWIG
$container['view'] = function ($c) {
    $settings = $c->get('settings');
    $view = new \Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

$container['jsonRender'] = function ($c) {
    $view = new App\Helper\JsonRenderer();

    return $view;
};

$container['jsonRequest'] = function ($c) {
    $jsonRequest = new App\Helper\JsonRequest();

    return $jsonRequest;
};

$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        $view = new App\Helper\JsonRenderer();
        return $view->render($response, 405,
            ['error_code' => 'not_allowed', 'error_message' => 'Method must be one of: ' . implode(', ', $methods)]
        );
    };
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $view = new App\Helper\JsonRenderer();
        return $view->render($response, 404, ['error_code' => 'not_found', 'error_message' => 'Not Found']);
    };
};

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {

        $settings = $c->settings;
        $view = new App\Helper\JsonRenderer();

        $errorCode = 500;
        if (is_numeric($exception->getCode()) && $exception->getCode() > 300 && $exception->getCode() < 600) {
            $errorCode = $exception->getCode();
        }

        if ($settings['displayErrorDetails'] == true) {
            $data = [
                'error_code' => $errorCode,
                'error_message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        } else {
            $data = [
                'error_code' => $errorCode,
                'error_message' => $exception->getMessage()
            ];
        }

        return $view->render($response, $errorCode, $data);
    };
};

$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        return $next($request, $response);
    });
    return $guard;
};

// FLASH MESSAGES
$container['flash'] = function ($c) {
    return new \Slim\Flash\Messages;
};

// DATABASE
use Illuminate\Database\Capsule\Manager as Capsule;

$settings = $container->get("settings");
$capsule = new Capsule;
$capsule->addConnection($settings["database"]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// MONOLOG
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new \Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
    return $logger;
};

$container['hash'] = function ($c) {
    return new App\Helper\Hash($c->get('app'));
};

//SESSION
$container['session'] = function ($c) {
    return new App\Helper\Session;
};

$container['App\Controller\Admin'] = function ($c) {
    return new App\Controller\Admin($c->get('view'), $c->get('logger'), $c->get('session'));
};
