<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\User;
	
class UserController extends Controller
{
	public function register() 
    {
        $user = new User();

        if ($user->verifyAuth()) {
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

            // Валидация
            if (Request::validate($validator)) {
                try {
                    // Отправляем случайный код в Redis
                    $code = mt_rand(100000, 999999);
                    $dataUser = ['email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT)];
                    $this->redis()->setex(hash('sha256', $code), 3600 * 12, serialize($dataUser));

                    $data = [
                        'from' => 'iwpoo23@gmail.com',
                        'from_name' => 'DIO',
                        'to' => $email,
                        'subject' => 'Подтверждение почты',
                        'body' => '',
                        'html' => "
                            <div style='text-align: center;'>
                                <div style='font-weight: bold; font-size: 32px; font-family: Arial, sans-serif;'>DIO</h1>
                                <div style='font-size: 24px; font-family: Arial, sans-serif; color: #232323; font-weight: bold;'>Код для подтверждения почты</div>
                                <div style='font-weight: bold; font-size: 32px; font-family: Arial, sans-serif;'>$code</div>
                            </div> 
                        "
                    ];
                    
                    // Отправляем код на почту
                    $this->mail('iwpoo23@gmail.com', 'jifpsnwtcrtodnhj', $data);
                } catch (Exception $e) {
                    Request::$errors['mail'] = $this->mailException();
                }   

                $this->redirect('/register/verification');
            }
        }

        return $this->render('auth/register.twig', [
            'errors' => Request::$errors,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function registerVerification()
    {
        $user = new User();
        $db = User::getLink(); 
        
        $error = '';

        if (isset($_POST['send-code'])) {
            $code = $this->add('code');
            $encrypted_code = hash('sha256', $code);  
            $code_redis = $this->redis()->get($encrypted_code);

            $validator = [
                Request::validateCsrfToken()
            ];
            
            // Если код и валидация корректна, то регистрируем пользователя и авторизовываем заодно
            if ($code_redis and Request::validate($validator)) {
                // Начало транзакции
                $db->beginTransaction();

                try {
                    // Сохраняем данные регистрации в БД
                    $data = unserialize($code_redis);
                    $user->create([
                        'email' => $data['email'],
                        'password' => $data['password'],
                    ]);
        
                    // Получаем id с последней добавленной записи
                    $id = User::getLink()->lastInsertId();

                    // Авторизовываем
                    $user->auth(['id' => $id, 'email' => $data['email']]);

                    // Удаляем токен из Redis
                    $this->redis()->del($encrypted_code);

                    $db->commit();
                    $this->redirect('/');
                } catch(PDOException $e) {
                    // Откат транзакции
                    $db->rollBack();
                }
            } else {
                $error = 'Код для подтверждения почты не верный или устарел.';
            }
        }

        return $this->render('auth/register_verification.twig', [
            'error' => $error,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function login() 
    {
        $userModel = new User;
        $auth_error = '';

        if ($userModel->verifyAuth()) {
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
                $userModel->auth(['id' => $user['id'], 'email' => $user['email']]);
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

    public function logout()
    {
        $user = new User;

        if (isset($_POST['logout'])) {
            // Получаем токен из куки и шифруем его для сравнения токена из Redis
            $token = $_COOKIE['session_token'];
            $key = '5fQ-1VloNNPMsvo5HK_ylkX^%9%5+=B8';
            $iv = 'iI^WL%GPVow6Ae3t';
            $encrypted_token = openssl_encrypt($token, 'AES-128-CBC', $key, 0, $iv);
        
            // Если данный токен существует, то удаляем его из Redis и очищаем куки
            $this->redis()->del($encrypted_token);
            setcookie('session_token', '', time() - 86400 * 30, '/');

            // Перенаправляем на страницу авторизации
            $this->redirect('/login');
        }        
    }
}