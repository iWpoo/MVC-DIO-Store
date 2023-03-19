<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProductsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('products');
        $table->addColumn('user_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('category', 'string', ['limit' => 255, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('title', 'string', ['limit' => 255, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('image', 'string', ['limit' => 255, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('price', 'float', ['null' => FALSE])
              ->addColumn('currency', 'string', ['limit' => 55, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('color' , 'string', ['limit' => 55, 'null' => TRUE, 'collation' => 'utf8_general_ci'])
              ->addColumn('memory' , 'string', ['limit' => 55, 'null' => TRUE, 'collation' => 'utf8_general_ci'])
              ->addColumn('created_at', 'timestamp', ['null' => TRUE, 'default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['user_id', 'title', 'category'])
              ->addForeignKey('user_id', 'users', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();
    }
}
