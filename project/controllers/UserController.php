<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\ResetCode;
use App\Project\Models\User;
use Predis\Client;
	
class UserController extends Controller
{
	public function register () 
    {
        $user = new User();
        
        $auth = $user->verifyAuth();
        if ($auth) {
            $this->redirect('/');
        }

        if (isset($_POST['register'])) {
            $email = $this->add('email');
            $password = $this->add('password');
            $confirm_password = $this->add('confirm_password');

            $validator = [
                Request::validateEmail($email),
                Request::validateUnique($email, 'users', 'email', 'unique', 'Данная почта уже используется.'),
                Request::validateMinLine($password, 8, 'min_pass', 'Пароль должен содержать не менее 8 символов.'),
                Request::validateMaxLine($password, 99, 'max_pass', 'Пароль не должен превышать больше 99 символов.'),
                Request::validateConfirmPassword($password, $confirm_password),
                Request::validateCsrfToken(),
            ];

            if (Request::validate($validator)) {
                $user->create([
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                ]);

                $this->redirect('/login');
            }
        }

        return $this->render('auth/register.twig', [
            'errors' => Request::$errors,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function login() {
        $userModel = new User;
        $auth_error = '';

        $auth = $userModel->verifyAuth();
        if ($auth) {
            $this->redirect('/');
        }
        
        if (isset($_POST['login'])) {
            $email = $this->add('email');
            $password = $this->add('password');

            $validator = [
                Request::validateCsrfToken()
            ];

            $user = $userModel->find($email, 'email');

            if (Request::validate($validator) and $user and password_verify($password, $user['password'])) {
                $userModel->auth($user['email']); 
                $this->redirect('/');
            } else {
                $auth_error = "Неверный email или пароль.";
            }
        }

        return $this->render('auth/auth.twig', [
            'csrf_token' => $this->generateCsrfToken(),
            'auth_error' => $auth_error,
        ]);
    }

    public function index() {
        $user = new User;
        $auth = $user->verifyAuth();

        if ($auth) {
            echo "<h1>Hello, $auth!</h1>";
        } else {
            $this->redirect('/login');
        }
    }
}