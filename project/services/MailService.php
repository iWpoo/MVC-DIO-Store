<?php 

namespace App\Project\Services;

use App\Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
        protected static $link;

        public function __construct()
        {
                self::$link = Database::getInstance()->getPdo();
        }

        // PHPMailer
	public function mail($username, $password, array $data)
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

	public function mailException()
	{
		$mail = new PHPMailer(true);
                return 'Письмо не отправлено. Ошибка: ' . $mail->ErrorInfo;
	}
}
