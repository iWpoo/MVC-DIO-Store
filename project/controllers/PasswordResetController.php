<?php

namespace App\Project\Controllers;

use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\User;

class PasswordResetController extends BaseController
{
	public function sendResetToken() 
    {
        $error = ''; 
        $success = ''; 

        if (isset($_POST['send-token'])) {
            $email = $this->add('email');
                   
            $validator = [
                Request::validateUnique($email, 'users', 'email', 'unique'),
                Request::validateCsrfToken()
            ];
            
            // Проверяем существует ли профиль с данной почтой
            if (!Request::validate($validator)) {
                try {
                    // Отправляем случайный токен в БД
                    $token = bin2hex(random_bytes(16));
                    $this->redisService->redis()->setex(hash('sha256', $token), 86400, $email);

                    $data = [
                        'from' => 'iwpoo23@gmail.com',
                        'from_name' => 'DIO',
                        'to' => $email,
                        'subject' => 'Восстановления пароля',
                        'body' => '',
                        'html' => "
                            <div style='text-align: center;'>
                                <div style='font-weight: bold; font-size: 32px; font-family: Arial, sans-serif;'>DIO</h1>
                                <div style='font-size: 24px; font-family: Arial, sans-serif; color: #232323; font-weight: bold;'>Перейдите по ссылке для восстановления пароля</div>
                                <a href='http://localhost:8000/change/password/reset/$token'><button style='background: #007BFF; color: white; font-weight: bold; font-size: 16px; font-family: Arial; border: none; outline: none; padding: 10px; border-radius: 4px;'>Восстановить пароль</button></a>
                            </div> 
                        "
                    ];
        
                    $this->mailService->mail('iwpoo23@gmail.com', 'jifpsnwtcrtodnhj', $data);

                    $success = 'Проверьте вашу почту.';
                } catch (Exception $e) {
                    $error = $this->mailService->mailException();
                }   
            } else {
                $error = 'Профиль пользователя не найден.';
            }
        }

        return $this->render('auth/password/password-reset.twig', [
            'error' => $error,   
            'success' => $success,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function changePass($params)
    {
        $user = new User();  

        // Шифруем токен и добавляем в Redis
        $encrypted_token = hash('sha256', $params['token']);  
        $token = $this->redisService->redis()->get($encrypted_token);  
        
        // В случае если токен не верный или устарел
        if (!$token) {
            echo "<h3 style='text-align: center; font-family: Arial;'>
                Ссылка для восстановления пароля не верный или устарел.
            </h3>";
            exit();
        }

        if (isset($_POST['change-pass'])) {
            $new_pass = $this->add('new_password');
            $confirm_pass = $this->add('confirm_password');

            $validator = [
                Request::validateMinLine($new_pass, 8, 'min_pass', 'Пароль должен содержать не менее 8 символов.'),
                Request::validateMaxLine($new_pass, 99, 'max_pass', 'Пароль не должен превышать больше 99 символов.'),
                Request::validateConfirmPassword($new_pass, $confirm_pass),
                Request::validatePasswordsMatch($token, 'users', 'email', $new_pass),
                Request::validateCsrfToken()
            ];

            if (Request::validate($validator)) {
                // Меняем пароль на новый
                $user->update($token, 'email', [
                    'password' => password_hash($new_pass, PASSWORD_DEFAULT)
                ]); 
                $this->redisService->redis()->del($encrypted_token);

                if ($this->authService->verifyAuth()) {
                    $this->redirect('/change/password');   
                } else {
                    $this->redirect('/login');
                }
            }
        }

        return $this->render('auth/password/change_pass.twig', [
            'errors' => Request::$errors,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
}
