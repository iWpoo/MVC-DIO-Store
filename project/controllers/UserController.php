<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\User;
use App\Project\Services\AuthService;
use App\Project\Services\RedisService;
use App\Project\Services\MailService;
	
class UserController extends Controller
{
    protected $authService;
    protected $redisService;
    protected $mailService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->redisService = new RedisService();
        $this->mailService = new MailService();
    }

	public function register() 
    {
        if ($this->authService->verifyAuth()) {
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
                    $dataUser = [
                        'email' => $email, 
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'first_name' => null,
                        'last_name' => null,
                        'address' => null,
                        'phone' => null,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->redisService->redis()->setex(hash('sha256', $code), 3600 * 12, serialize($dataUser));

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
                    $this->mailService->mail('iwpoo23@gmail.com', 'jifpsnwtcrtodnhj', $data);
                } catch (Exception $e) {
                    Request::$errors['mail'] = $this->mailService->mailException();
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
            $code_redis = $this->redisService->redis()->get($encrypted_code);

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
                    $this->authService->auth([
                        'id' => $id, 
                        'email' => $data['email'],
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'address' => $data['address'],
                        'phone' => $data['phone'],
                        'created_at' => $data['created_at']
                    ]);

                    // Удаляем токен из Redis
                    $this->redisService->redis()->del($encrypted_code);

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

        if ($this->authService->verifyAuth()) {
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
                $this->authService->auth([
                    'id' => $user['id'], 
                    'email' => $user['email'], 
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'address' => $user['address'],
                    'phone' => $user['phone'],
                    'created_at' => $user['created_at']
                ]);
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
        if (isset($_POST['logout'])) {
            // Получаем токен из куки и шифруем его для сравнения токена из Redis
            $token = $_COOKIE['session_token'];
            $key = '5fQ-1VloNNPMsvo5HK_ylkX^%9%5+=B8';
            $iv = 'iI^WL%GPVow6Ae3t';
            $encrypted_token = openssl_encrypt($token, 'AES-128-CBC', $key, 0, $iv);
        
            // Если данный токен существует, то удаляем его из Redis и очищаем куки
            $this->redisService->redis()->del($encrypted_token);
            setcookie('session_token', '', time() - 86400 * 30, '/');

            // Перенаправляем на страницу авторизации
            $this->redirect('/login');
        }        
    }
}