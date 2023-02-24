<?php
namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Controller
{	
	// Render the twig pages	
	protected function render($view, $data = []) {
		$viewPath = $_SERVER['DOCUMENT_ROOT'] . "/project/views/";
        $loader = new FilesystemLoader($viewPath);
        $twig = new Environment($loader);

		return $twig->render($view, $data);
	}
    
	// JSON
	protected function response($data) {
        $json = json_encode($data);
        header('Content-Type: application/json');
        return $json;
	}
    
	// Add element data
	protected function add($element, string $way = 'htmlspecialchars')
	{
		if ($way === 'htmlspecialchars') {
            return htmlspecialchars(trim($_POST[$element]));
		} else if ($way === 'strip_tags') {
			return strip_tags(trim($_POST[$element]));
		}
	}

    // Upload files
	protected function uploadFile($file, $filename)
	{
		$destination = $_SERVER['DOCUMENT_ROOT'] . '/project/webroot/uploads/' . $filename;
		move_uploaded_file($file['tmp_name'], $destination);
    }

	protected function uniqueNameFile($file, $allowed_extensions = ["png", "jpg", "mp4", "gif", "jpeg"])
	{
		$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
		$filename = bin2hex(random_bytes(32)) . '.' . $file_extension;
		return $filename;
	}
    
	// Redirect
	protected function redirect($to)
	{
        header("Location: $to");
		return $this;
	}

	// CSRF token
	protected function generateCsrfToken() {
        if(!isset($_SESSION['csrf_token'])){
          $token = bin2hex(random_bytes(32));
          $_SESSION['csrf_token'] = $token;
        } else {
			$token = $_SESSION['csrf_token'];
		}
        return $token;
    }

    // JWT
	protected function createToken($user_id, $secret_key, $alg) 
	{
		$payload = array(
			"user_id" => $user_id,
			"exp" => time() + 3600 * 24 * 30 // JWT будет действителен в течение 1 месяца
		);
		$jwt = JWT::encode($payload, $secret_key, $alg); 
		return $jwt;
	}

	protected function verifyToken($token, $secret_key, $alg) 
	{
        try {
			$decoded = JWT::decode($token, new Key($secret_key, $alg));
			return $decoded;
		} catch (Exception $e) {
			return false;
		}
	}

	// PHPMailer
	protected function mail($username, $password, array $data)
	{
		// Создаем новый объект PHPMailer
		$mail = new PHPMailer(true);
		$mail->CharSet = 'UTF-8';

		// Настраиваем SMTP
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = $username;  
		$mail->Password = $password;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;

		// Настраиваем отправителя и получателя
		$mail->setFrom($data['from'], $data['from_name']); 
		$mail->addAddress($data['to']);

		// Настраиваем содержимое письма
		$mail->isHTML(true);
		$mail->Subject = $data['subject'];
		$mail->Body = $data['body'];

		// Добавляем HTML-шаблон в сообщение
        $mail->msgHTML($data['html']);

		// Отправляем письмо
		$mail->send();
	}
}