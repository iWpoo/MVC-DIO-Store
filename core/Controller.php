<?php
namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

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
	protected function add($element)
	{
        return htmlspecialchars(trim($_POST[$element]));
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
}