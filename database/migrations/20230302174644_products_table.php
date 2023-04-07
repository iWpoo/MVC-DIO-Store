<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProductsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('products');
        $table->addColumn('user_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('title', 'string', ['limit' => 255, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('category_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('subcategory_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addColumn('image', 'string', ['limit' => 255, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('price', 'float', ['null' => FALSE])
              ->addColumn('color', 'string', ['limit' => 55, 'null' => TRUE, 'collation' => 'utf8_general_ci'])
              ->addColumn('memory', 'string', ['limit' => 55, 'null' => TRUE, 'collation' => 'utf8_general_ci'])
              ->addColumn('sales', 'integer', ['limit' => 255, 'null' => FALSE, 'default' => 0])
              ->addColumn('created_at', 'timestamp', ['null' => TRUE, 'default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['user_id', 'title', 'category_id', 'subcategory_id'])
              ->addForeignKey('user_id', 'users', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();


        /* Записи товаров в качестве примера для тестирования,
        при ненадомности можно удалить последующий код */
        $data = [
            [
                'user_id' => 1,
                'title' => 'Xiaomi Mi 13 Lite',
                'category_id' => 2,
                'subcategory_id' => 7,
                'image' => 'example1.png',
                'price' => 33200,
                'color' => 'BLACK',
                'memory' => '8/128GB',
                'sales' => 23
            ],
            [
                'user_id' => 1,
                'title' => 'Honor Magic Watch 2 42 mm',
                'category_id' => 5,
                'subcategory_id' => 26,
                'image' => 'example3.jpg',
                'price' => 8500,
                'color' => 'BLACK',
                'sales' => 11
            ],
            [
                'user_id' => 1,
                'title' => 'Iphone XS Max',
                'category_id' => 2,
                'subcategory_id' => 5,
                'image' => 'example4.jpeg',
                'price' => 44500,
                'color' => 'WHITE',
                'memory' => '4/64GB',
                'sales' => 56
            ],
            [
                'user_id' => 1,
                'title' => 'Honor 8A Prime',
                'category_id' => 2,
                'subcategory_id' => 13,
                'image' => 'example6.png',
                'price' => 6600,
                'color' => 'BLACK',
                'memory' => '3/64GB',
                'sales' => 9
            ],
            [
                'user_id' => 1,
                'title' => 'Xiaomi Mi Smart Band 7',
                'category_id' => 5,
                'subcategory_id' => 27,
                'image' => 'example9.jpg',
                'price' => 3000,
                'color' => 'BLACK',
                'sales' => 44
            ],
            [
                'user_id' => 1,
                'title' => 'Беспроводные наушники Xiaomi Redmi Buds 3 Lite',
                'category_id' => 4,
                'subcategory_id' => 22,
                'image' => 'example10.jpg',
                'price' => 1690,
                'color' => 'BLACK',
                'sales' => 97
            ],
            [
                'user_id' => 1,
                'title' => 'Умная колонка Xiaomi Smart Speaker IR Control',
                'category_id' => 4,
                'subcategory_id' => 23,
                'image' => 'example11.jpg',
                'price' => 3490,
                'sales' => 78
            ],
            [
                'user_id' => 1,
                'title' => 'Ноутбук Xiaomi Mi Notebook Pro 15" Enhanced Edition',
                'category_id' => 8,
                'subcategory_id' => 37,
                'image' => 'example12.jpg',
                'price' => 55200,
                'sales' => 69
            ],
            [
                'user_id' => 1,
                'title' => 'Яндекс Станция 2 + Алиса',
                'category_id' => 6,
                'subcategory_id' => 29,
                'image' => 'example14.png',
                'price' => 22900,
                'sales' => 133
            ],
        ];
              
        $table->insert($data)->save();
    }
}
