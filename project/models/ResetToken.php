<?php 

namespace App\Project\Models;
use App\Core\Model;

class ResetToken extends Model
{
    protected $table = 'password_reset_tokens';

    public function checkToken($value)
    {
        $query = self::$link->prepare("SELECT * FROM ".$this->table." WHERE token = :value AND expiration > NOW()");
        $query->execute([':value' => $value]);
        return $query->fetch();
    }
}