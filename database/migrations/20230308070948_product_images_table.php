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


        /* Записи в качестве примера для тестирования,
        при ненадомности можно удалить последующий код */

        $data = [
            [
                'product_id' => 1,
                'image' => 'example2.png'
            ],
            [
                'product_id' => 3,
                'image' => 'example5.jpeg'
            ],
            [
                'product_id' => 4,
                'image' => 'example7.png'
            ],
            [
                'product_id' => 4,
                'image' => 'example8.png'
            ],
            [
                'product_id' => 8,
                'image' => 'example13.jpg'
            ],
            [
                'product_id' => 9,
                'image' => 'example16.png'
            ],
            [
                'product_id' => 9,
                'image' => 'example16.png'
            ],
        ];

        $table->insert($data)->save();
    }
}
