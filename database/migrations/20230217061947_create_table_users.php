<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('role', 'enum', ['values' => ['user', 'admin'], 'default' => 'user', 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('first_name', 'string', ['limit' => 55, 'null' => TRUE, 'collation' => 'utf8_general_ci'])
              ->addColumn('last_name', 'string', ['limit' => 55, 'null' => TRUE, 'collation' => 'utf8_general_ci'])
              ->addColumn('email', 'string', ['null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('password', 'string', ['null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('address', 'text', ['null' => TRUE, 'collation' => 'utf8_general_ci'])
              ->addColumn('phone', 'string', ['null' => TRUE, 'collation' => 'utf8_general_ci'])
              ->addColumn('created_at', 'timestamp', ['null' => TRUE, 'default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['email'], ['unique' => TRUE])
              ->create();

        // Запись в качестве примера для тестирования. Последующий код можно удалить
        
        $data = [
            [
                'role' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
            ]
        ];

        $table->insert($data)->save();
    }
}
