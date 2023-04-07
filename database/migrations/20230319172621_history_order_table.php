<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class HistoryOrderTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('order_history');
        $table->addColumn('order_num', 'integer', ['limit' => 11, 'null' => FALSE])
              ->addColumn('user_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('payment_method', 'string', ['limit' => 55, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('total_price', 'float', ['limit' => 11, 'null' => FALSE])
              ->addColumn('address', 'string', ['limit' => 255, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('status', 'enum', ['values' => ['Доставлен', 'Отменен'], 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('created_at', 'string', ['null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addIndex(['user_id'])
              ->addIndex(['order_num'], ['unique' => TRUE])
              ->addForeignKey('user_id', 'users', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();
    }
}
