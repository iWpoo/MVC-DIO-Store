<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CartsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('carts');
        $table->addColumn('user_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('product_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('count', 'integer', ['limit' => 11, 'null' => FALSE, 'default' => 1])
              ->addColumn('price', 'float', ['limit' => 11, 'null' => FALSE])
              ->addColumn('expire', 'timestamp', ['null' => TRUE, 'default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['user_id', 'product_id'])
              ->addForeignKey('user_id', 'users', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->addForeignKey('product_id', 'products', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();
    }
}
