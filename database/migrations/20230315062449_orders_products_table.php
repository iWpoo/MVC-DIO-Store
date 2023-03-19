<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OrdersProductsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('orders_products');
        $table->addColumn('order_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('product_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('quantity', 'integer', ['limit' => 11, 'null' => FALSE, 'default' => 1])
              ->addColumn('price', 'float', ['limit' => 11, 'null' => FALSE])
              ->addIndex(['order_id', 'product_id'])
              ->addForeignKey('order_id', 'orders', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->addForeignKey('product_id', 'products', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();
    }
}
