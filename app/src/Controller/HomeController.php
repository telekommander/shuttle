<?php

namespace App\Controller;

use Carlosocarvalho\SimpleInput\Input\Input;
use App\Model\User;
use App\Validation\Validator;

/**
 * Class HomeController
 * @package App\Controller
 */
final class HomeController extends AbstractController
{
    public function dispatch($request, $response, $params)
    {
        // SAMPLE LOGGER OUTPUT
        $this->logger->info("Example Homepage action dispatched");
        
        $acl        = new \App\Helper\Acl;
        return $this->view->render($response, "home.twig", [
            "user"          => User::all(),
            "currentuser"   => $acl->profile()
        ]);
    }

    public function dashboard($request, $response, $params)
    {
        $session    = new \App\Helper\Session;
        $flash      = $session->get("flash");
        return $this->view->render($response, "dashboard.twig", ["flash" => $flash ] );
    }

    public function register($request, $response, $params)
    {
        $return = $this->view->render($response, "register.twig");
        return $return;
    }

    public function testJson($request, $response, $params)
    {
        $option   = [$params , "foo" => "bar"];
        $response = $this->response
            ->withJson($option)
            ->withStatus(201);
        return $response;
    }
}