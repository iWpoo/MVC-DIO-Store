<?php
namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Predis\Client;

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

	protected function mailException()
	{
		$mail = new PHPMailer(true);
        return 'Письмо не отправлено. Ошибка: ' . $mail->ErrorInfo;
	}
    
	// Redis
	public function redis()
    {
        $redis = new Client([
            'scheme' => REDIS_SCHEME,
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
        ]);
        return $redis;
    }
}