<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FavouritesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('favorites');
        $table->addColumn('user_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('product_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addIndex(['user_id', 'product_id'])
              ->addForeignKey('user_id', 'users', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->addForeignKey('product_id', 'products', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();
    }
}
