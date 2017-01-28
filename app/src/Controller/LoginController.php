<?php

namespace App\Controller;

use App\Helper\Hash;
use Carlosocarvalho\SimpleInput\Input\Input;
use App\Model\User;
use App\Validation\Validator;
use Violin\Violin;
use App\Helper\Acl;
use App\Helper\Session;

/**
 * Class LoginController
 * @package App\Controller
 */
final class LoginController extends AbstractController
{
    public function index()
    {

    }

    public function login($request, $response, $params)
    {
        $this->logger->info("Login dispatched");
        $this->debugger->addMessage("Login dispatched");
        return $this->view->render($response,'login.twig',
            ['csrf' => [
                'name' => $request->getAttribute('csrf_name'),
                'value' => $request->getAttribute('csrf_value'),
            ],
        ]);
    }

    public function loginPost($request, $response, $params)
    {
        $return = "";
        $identifier = Input::post('identifier');
        $password = Input::post('password');
        $v = new Validator(new User);
        $v->validate([
            'identifier' => [$identifier, 'required|email'],
            'password' => [$password, 'required']
        ]);
        if ($request->getAttribute('csrf_status') === false) {
            $flash = 'CSRF failure';
            $return = $this->view->render($response, 'login.twig', ['errors' => $v->errors(), 'flash' => $flash, 'request' => $this->request]);
        } else {
            if ($v->passes())
            {
                $user = User::where('username', $identifier)->orWhere('email', $identifier)->first();
                if ($user && $this->hash->passwordCheck($password, $user->password))
                {
                    $this->session->set($this->auth['session'],$user->id);
                    $this->session->set($this->auth['group'],$user->group_id);
                    return $response->withRedirect('dashboard');
                }
                else
                {
                    $flash = 'Sorry, you couldn\'t be logged in.';
                    $this->view->render($response,'login.twig', ['errors' => $v->errors(), 'flash' => $flash, 'request' => $this->request]);
                }
            }
            else
            {
                $return = $this->view->render($response, 'login.twig',
                    ['errors' => $v->errors(),
                        'request' => $this->request,
                        'csrf' => [
                            'name' => $request->getAttribute('csrf_name'),
                            'value' => $request->getAttribute('csrf_value'),
                        ],
                    ]);
            }
        }

        return $return;
    }

    public function logout($request, $response, $params)
    {
        $session = new \App\Helper\Session;
        $session::destroy();
        return $this->response->withRedirect('login');
    }

    public function registerPost($request, $response, $params)
    {
        $email = Input::post('email');
        $username = Input::post('username');
        $password = Input::post('password');

        $passwordConfirm = Input::post('password_confirm');
        $v = new Validator(new User);
        $v->validate([
            'email' => [$email, 'required|email|uniqueEmail'],
            'username' => [$username, 'required|alnumDash|max(20)|uniqueUsername'],
            'password' => [$password, 'required|min(6)'],
            'password_confirm' => [$passwordConfirm, 'required|matches(password)']
        ]);

        if ($v->passes()) {
            $user = new User();
            $user->email = $email;
            $user->username = $username;
            $user->password = $this->hash->password($password);
            $user->group_id = 3;
            $user->save();
            $flash = "You have been registered.";
        } else {
            $flash = "registration failed.";
        }
        $return = $this->view->render($response, 'register.twig', ['errors' => $v->errors(), 'flash' => $flash, 'request' => $this->request]);
        return $return;
    }

}