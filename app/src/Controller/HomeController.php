<?php

namespace App\Controller;

use MartynBiz\Slim3Controller\Controller;
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
        $this->logger->info("Example Homepage action dispatched");
        $acl        = new \App\Helper\Acl;
        return $this->view->render($response, "home.twig", [
            'user'          => User::all(),
            'currentuser'   => $acl->profile()
        ]);
    }

    public function dashboard()
    {
        $session    = new \App\Helper\Session;
        $flash      = $session->get('flash');
        return $this->render('dashboard.twig', ['flash' => $flash ] );
    }

    public function register()
    {
        $return = $this->render('register.twig');
        return $return;
    }

    public function testJson($args)
    {
        $option   = [$args , "foo" => "bar"];
        $response = $this->response
            ->withJson($option)
            ->withStatus(201);
        return $response;
    }
}