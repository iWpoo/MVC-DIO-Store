<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\ResetCode;
use App\Project\Models\User;
	
class UserController extends Controller
{
	public function register () 
    {
        $user = new User();
        
        if (isset($_COOKIE['token'])) {
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

        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');   
        }
        
        if (isset($_POST['login'])) {
            $email = $this->add('email');
            $password = $this->add('password');

            $user = $userModel->find($email, 'email');
            if ($user and password_verify($password, $user['password'])) {
                $token = $this->createToken($user['id'], '$XhmMR&$mZNy-ouTngs&%aQcf+2Y3brc', 'HS256'); 
                
                // Шифрование токена
                $encryption_key = '+i"�]��#�j�T��Vf|hU#H��Gh�';
                $iv = 'eyF574i1v!Z_3tsK';
                $encrypted_jwt = openssl_encrypt($token, "AES-256-CBC", $encryption_key, 0, $iv);
		        setcookie('auth_token', $encrypted_jwt, time() + 3600 * 24 * 30, '/', 'localhost', false, true);
                $this->redirect('/');
            } else {
                $auth_error = "Неверный email или пароль.";
            }
        }

        return $this->render('auth/auth.twig', [
            'auth_error' => $auth_error,
        ]);
    }

    public function index() {
        if (!isset($_COOKIE['auth_token'])) {
            $this->redirect('/login');
        }

        if (!isset($_SESSION['user_id'])) {
            $encryption_key = '+i"�]��#�j�T��Vf|hU#H��Gh�';
            $iv = 'eyF574i1v!Z_3tsK';
            $encrypted_jwt = $_COOKIE["auth_token"];
            $jwt = openssl_decrypt($encrypted_jwt, "AES-256-CBC", $encryption_key, 0, $iv);
            
            $decoded = $this->verifyToken($jwt, '$XhmMR&$mZNy-ouTngs&%aQcf+2Y3brc', 'HS256');
            $user_id = $decoded->user_id;
    
            $_SESSION["user_id"] = $user_id;
            }

        if (isset($_SESSION['user_id'])) {
            $userModel = new User;
            $user = $userModel->findAll();
            foreach ($user as $item) {
                echo $item['email'] . "<br>";
            }
        } else {
            $this->redirect('/login');
        }
    }
}