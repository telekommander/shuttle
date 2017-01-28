<?php

$container = $app->getContainer();

// TWIG
$container['view'] = function ($c) {
    $settings = $c->get('settings');
    $view = new \Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new \App\Util\TwigExtension(
        $c['router'],
        $c['request']->getUri(),
        $c['debugger']
    ));

    $view->addExtension(new Twig_Extension_Debug());

    if(App\Helper\Acl::isLogged()) {
        $view->getEnvironment()->addGlobal("logged", "no");
        $view->getEnvironment()->addGlobal("user", $c->get('user'));
    }

    $view->getEnvironment()->addGlobal("site", $c->get('site'));
    $view->getEnvironment()->addGlobal("debug", $settings['mode']);

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

// CSFR GUARD
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
$container['db'] = function ($c) {
    $settings = $c->get('settings')['database'];
    return new \App\Util\EloquentService($settings);
};

// MAILER
$container["mailer"] = function ($c) {
    $settings = $c->get("settings")["mail"];
    $mailer = new \App\Util\SwiftMailerService(
        $c->get("settings")["mode"],
        $settings["transport"],
        $settings["options"]
    );
    return $mailer;
};

// MONOLOG
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new \Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
    return $logger;
};

// HASH
$container['hash'] = function ($c) {
    return new App\Helper\Hash($c->get('crypt'));
};

//SESSION
$container['session'] = function ($c) {
    return new App\Helper\Session;
};

$container['App\Controller\Admin'] = function ($c) {
    return new App\Controller\Admin($c->get('view'), $c->get('logger'), $c->get('session'));
};

// USER
$container['user'] = function ($c) {
    $acl = new \App\Helper\Acl();
    $user= $acl->profile();
    return $user;
};

// DEBBUGER
$container['debugger'] = function ($c) {
    return new \App\Util\DebuggerService(
        $c->get('settings')['mode'],
        $c->get('db')->getConnection(),
        $c->get('mailer')->getMailer(),
        $c->get('logger')
    );
};
$container->get('debugger');

foreach (glob( __DIR__ . "/../src/Controller/*.php") as $filename)
{
    $ctrl = basename($filename, ".php");
    $type = '\\App\\Controller\\' . $ctrl;
    $field = str_replace('\App\Controller','App',$type);
    $container[$field] = function ($c) use($type) {
        return new $type($c);
    };
}