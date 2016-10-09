<?php

namespace App\Controller;

use MartynBiz\Slim3Controller\Controller;
use Carlosocarvalho\SimpleInput\Input\Input;
use App\Model\User;
use App\Validation\Validator;

/**
 * Class LoginController
 * @package App\Controller
 */
class LoginController extends Controller
{
    public function index()
    {

    }

    public function login()
    {
        return $this->render('login.twig',
            ['csrf' => [
                'name' => $this->request->getAttribute('csrf_name'),
                'value' => $this->request->getAttribute('csrf_value'),
                ],
            ]);
    }

    public function loginPost()
    {
        $return     = "";
        $identifier = Input::post('identifier');
        $password   = Input::post('password');
        $v          = new Validator(new User);
        $v->validate([
            'identifier'    => [$identifier, 'required|email'],
            'password'      => [$password, 'required']
        ]);
        if ($this->request->getAttribute('csrf_status') === false)
        {
            $flash = 'CSRF failure';
            $return = $this->render('login.twig',['errors' => $v->errors(),'flash' => $flash,'request' => $this->request]);
        }
        else
        {
            if($v->passes())
            {
                $user = User::where('username', $identifier)->orWhere('email', $identifier)->first();
                $hash = $this->get('hash');
                if($user && $hash->passwordCheck($password, $user->password))
                {
                    $session  = new \App\Helper\Session;
                    $auth = $this->get('auth');
                    $session->set($auth['session'],$user->id);
                    $session->set($auth['group'],$user->group_id);
                    $return = $this->redirect('dashboard');
                }
                else
                    {
                    $flash = 'Sorry, you couldn\'t be logged in.';
                    $this->render('login.twig',['errors' => $v->errors(),'flash' => $flash,'request' => $this->request]);
                }
            }
            else
            {
                $return = $this->render('login.twig',
                    ['errors' => $v->errors(),
                        'request' => $this->request,
                        'csrf' => [
                            'name' => $this->request->getAttribute('csrf_name'),
                            'value' => $this->request->getAttribute('csrf_value'),
                        ],
                    ]);
            }
        }

        return $return;
    }

    public function logout()
    {
        $session    = new \App\Helper\Session;
        $session::destroy();
        return $this->response->withRedirect('login');
    }

    public function registerPost()
    {
        $email      = Input::post('email');
        $username   = Input::post('username');
        $password   = Input::post('password');

        $passwordConfirm = Input::post('password_confirm');
        $v = new Validator(new User);
        $v->validate([
            'email'     => [$email, 'required|email|uniqueEmail'],
            'username'  => [$username, 'required|alnumDash|max(20)|uniqueUsername'],
            'password'  => [$password, 'required|min(6)'],
            'password_confirm' => [$passwordConfirm, 'required|matches(password)']
        ]);

        if ($v->passes()) {
            $hash = $this->get('hash');
            $user = new User();
            $user->email    = $email;
            $user->username = $username;
            $user->password = $hash->password($password);
            $user->group_id = 3;
            $user->save();
            $flash = "You have been registered.";
        }else{
            $flash = "registration failed.";
        }
        $return = $this->render('register.twig',['errors' => $v->errors(),'flash' => $flash,'request' => $this->request]);
        return $return;
    }
}