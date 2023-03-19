<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProductImagesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('product_images');
        $table->addColumn('product_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('image', 'string', ['limit' => 255, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addIndex(['product_id'])
              ->addForeignKey('product_id', 'products', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();
    }
}
