<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PasswordResetCodeTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('password_reset_tokens', ['id' => FALSE, 'primary_key' => ['token']]);
        $table->addColumn('token', 'string', ['limit' => 255, 'null' => FALSE])
              ->addColumn('email', 'string', ['limit' => 255, 'null' => FALSE])
              ->addColumn('expiration', 'datetime', ['null' => FALSE])
              ->addIndex(['email'])
              ->create();
    }
}
