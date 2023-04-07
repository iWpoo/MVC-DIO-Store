<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class HistoryOrderProductsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('order_history_products');
        $table->addColumn('order_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('product_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('quantity', 'integer', ['limit' => 11, 'null' => FALSE])
              ->addColumn('price', 'float', ['limit' => 11, 'null' => FALSE])
              ->addIndex(['order_id', 'product_id'])
              ->addForeignKey('order_id', 'order_history', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->addForeignKey('product_id', 'products', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();
    }
}
