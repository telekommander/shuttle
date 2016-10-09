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
class HomeController extends Controller
{
    public function dispatch()
    {
        // $examples = $this->get('model.example')->find();
        // print_r($this->request);
        $this->get('logger')->info("Example Homepage action dispatched");
        $acl        = new \App\Helper\Acl;
        return $this->render('home.twig', array(
            'user'          => User::all(),
            'currentuser'   => $acl->profile()
        ));
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