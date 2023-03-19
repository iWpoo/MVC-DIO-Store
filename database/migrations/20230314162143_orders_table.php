<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OrdersTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('orders');
        $table->addColumn('order_num', 'integer', ['limit' => 11, 'null' => FALSE])
              ->addColumn('user_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('payment_method', 'string', ['limit' => 55, 'null' => FALSE])
              ->addColumn('total_price', 'float', ['limit' => 11, 'null' => FALSE])
              ->addColumn('status', 'string', ['limit' => 255, 'null' => FALSE])
              ->addColumn('created_at', 'timestamp', ['null' => FALSE, 'default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['user_id'])
              ->addIndex(['order_num'], ['unique' => TRUE])
              ->addForeignKey('user_id', 'users', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();
    }
}
