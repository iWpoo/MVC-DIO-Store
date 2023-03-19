<?php 

namespace App\Project\Requests;
use \PDO;

class Request
{
    public static $errors = [];

    public static function validate(array $array)
    {
        foreach ($array as $elem) {
            if ($elem !== true) {
                return false;
            }
        }
        return true;
    }

    public static function validateMinLine($element, $min, $key = 'min', $errorMessage = "Некорректный кол-во символов.")
    {
        if (strlen($element) >= $min) {
            return true;
        }
        self::$errors[$key] = $errorMessage;
        return false;
    }

    public static function validateMaxLine($element, $max, $key = 'max', $errorMessage = "Некорректный кол-во символов.")
    {
        if (strlen($element) <= $max) {
            return true;
        }
        self::$errors[$key] = $errorMessage;
        return false;
    }

    public static function validateRequired($element, $key = 'required', $errorMessage = "Поле является обязательным.")
    {
        if (empty($element)) {
            self::$errors[$key] = $errorMessage;
            return false;
        }
        return true;
    }

    public static function validateEmail($email, $key = 'email', $errorMessage = "Некорректный email-адрес.")
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::$errors[$key] = $errorMessage;
            return false;
        }
        return true;
    }

    public static function validateConfirmPassword($password, $confirm_password, $key = 'confirm_pass', $errorMessage = "Пароли не совпадают.") 
    {
        if ($password === $confirm_password) {
            return true;
        }
        self::$errors[$key] = $errorMessage;
        return false;
    }

    public static function validatePasswordsMatch($input, $table, $column, $new_password, $key = 'passwords_match', $errorMessage = "Новый пароль не должен совпадать со старым.")
    {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.'', DB_USER, DB_PASS);
        $query = $pdo->prepare("SELECT * FROM $table WHERE $column = :input");
        $query->execute([':input' => $input]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        if (password_verify($new_password, $user['password'])) {
            self::$errors[$key] = $errorMessage;
            return false;
        }
        return true;
    }

    public static function validateUnique($input, $table, $column, $key = 'unique', $errorMessage = "Данный запись уже используется.") 
    {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.'', DB_USER, DB_PASS);
        $query = $pdo->prepare("SELECT * FROM $table WHERE $column = :input");
        $query->execute([':input' => $input]);
        if ($query->rowCount() > 0) {
            self::$errors[$key] = $errorMessage;
            return false;
        }
        return true;
    }

    public static function hasFile($file, $extensions = ["jpg", "png", "gif", "mp4", "jpeg"], $maxSize = 5000000) 
    {
        $allowedExtensions = $extensions;
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $maxFileSize = $maxSize;
        $allowedMimeTypes = array("image/jpeg", "image/png", "image/gif", "video/mp4", "application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document");

        if(!isset($file["name"])) {
            self::$errors['error_file'] = "No file selected.";
            return false;
        } else if ($file["error"] > 0) {
            switch ($file["error"]) {
                case 1:
                    $message = "File size exceeds upload_max_filesize directive in php.ini";
                    break;
                case 2:
                    $message = "File size exceeds MAX_FILE_SIZE directive in HTML form";
                    break;
                case 3:
                    $message = "File was only partially uploaded";
                    break;
                case 4:
                    $message = "No file was uploaded";
                    break;
                case 6:
                    $message = "Missing a temporary folder";
                    break;
                case 7:
                    $message = "Failed to write file to disk";
                    break;
                case 8:
                    $message = "A PHP extension stopped the file upload";
                    break;
                default:
                    $message = "Unknown error";
                    break;
            }
            self::$errors['error_file'] = $message;
            return false;
        } 
        else if (!in_array($extension, $allowedExtensions)) {
            self::$errors['error_file'] = "Invalid file extension. Allowed extensions: " . implode(", ", $allowedExtensions);
            return false;
        } 
        else if ($file["size"] > $maxFileSize) {
            self::$errors['error_file'] = "File size exceeds max allowed size of " . $maxFileSize . " bytes.";
            return false;
        }
        else if (!in_array($file["type"], $allowedMimeTypes)) {
            self::$errors['error_file'] = "Invalid file type. Allowed types: " . implode(", ", $allowedMimeTypes);
            return false;
        }

        return true;
    }

    public static function validateCsrfToken()
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            self::$errors['error_csrf'] = 'Invalid CSRF token';
            return false;
        }
        return true;
    }
}