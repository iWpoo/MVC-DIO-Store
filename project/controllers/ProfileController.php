<?php

namespace App\Project\Controllers;
use App\Core\Controller;
use App\Project\Requests\Request;
use App\Project\Models\User;
use App\Project\Models\Product;
use App\Project\Models\Order;
	
class ProfileController extends Controller
{
    public function profile()
    {
        $user = new User();
        $auth = $user->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        return $this->render('profile/profile.twig', [
            'user' => $user->find($auth['id'])
        ]);
    }

    public function editProfile()
    {
        $user = new User();
        $auth = $user->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        if (isset($_POST['edit_profile'])) {
            $fname = $this->add('first_name');
            $lname = $this->add('last_name');
            $address = $this->add('address');
            $phone = $this->add('phone');

            $validator = [
                Request::validateMaxLine($fname, 55, 'max_fname', 'Вы превысили максимальное кол-во символов.'),
                Request::validateMaxLine($lname, 55, 'max_lname', 'Вы превысили максимальное кол-во символов.'),
                Request::validateCsrfToken(),
            ];

            // Проверка на валидацию
            if (Request::validate($validator)) {
                // Изменение данные пользователя
                $user->update($auth['id'], 'id', [
                    'first_name' => $fname,
                    'last_name' => $lname,
                    'address' => $address,
                    'phone' => $phone
                ]);
            }
        }

        return $this->render('profile/edit_profile.twig', [
            'errors' => Request::$errors,
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $user->find($auth['id'])
        ]);
    }

    public function changePassword()
    {
        $user = new User();
        $auth = $user->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        if (isset($_POST['change_pass'])) {
            $old_pass = $this->add('old_password');
            $new_pass = $this->add('new_password');
            $confirm_pass =$this->add('confirm_password');

            $validator = [
                Request::validateMinLine($new_pass, 8, 'min_pass', 'Пароль должен содержать не менее 8 символов.'),
                Request::validateMaxLine($new_pass, 99, 'max_pass', 'Пароль не должен превышать больше 99 символов.'),
                Request::validateConfirmPassword($new_pass, $confirm_pass),
                Request::validatePasswordsMatch($auth['id'], 'users', 'id', $new_pass),
                Request::validateCsrfToken(),
            ];

            $old_password_match = [Request::validatePasswordsMatch($auth['id'], 'users', 'id', $old_pass)];

            if (Request::validate($validator) and !Request::validate($old_password_match)) {
                $user->update($auth['id'], 'id', [
                    'password' => password_hash($new_pass, PASSWORD_DEFAULT)
                ]);

                $this->redirect('/profile');
            } else if (Request::validate($old_password_match)){
                Request::$errors['old_pass_match'] = 'Старый пароль не верный.';
            }
        }

        return $this->render('profile/change_password.twig', [
            'errors' => Request::$errors,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function favorites()
    {
        $user = new User();
        $auth = $user->verifyAuth();

        if (!$auth) {
            $this->redirect('/login');
        }

        $products = new Product();

        return $this->render('profile/favorites.twig', [
            'products' => $products->getFavoritesByUser($auth['id']),
        ]);
    }

    public function activeOrders()
    {
        $user = new User();
        $auth = $user->verifyAuth();
        
        if (!$auth) {
            $this->redirect('/login');
        }

        $products = new Product();
        $order = new Order();

        return $this->render('profile/active_orders.twig', [
            'orders' => $order->getOrdersInfo($auth['id']),
            'products' => $products->getOrdersByUser($auth['id'])
        ]);
    }

    public function deleteProfile()
    {
        $user = new User;
        $auth = $user->verifyAuth();

        // Очистка токена и куки
        $token = $_COOKIE['session_token'];
        $key = '5fQ-1VloNNPMsvo5HK_ylkX^%9%5+=B8';
        $iv = 'iI^WL%GPVow6Ae3t';
        $encrypted_token = openssl_encrypt($token, 'AES-128-CBC', $key, 0, $iv);
        $this->redis()->del($encrypted_token);
        setcookie('session_token', '', time() - 86400 * 30, '/');

        // Удаление аккаунта
        $user->delete($auth['id'], 'id');

        // Перенаправляем пользователя на страницу авторизации
        $this->redirect('/login');
    }
}