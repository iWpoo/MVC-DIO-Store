<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SubCategoriesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('subcategories');
        $table->addColumn('name', 'string', ['limit' => 55, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('category_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false]) 
              ->addIndex('category_id')
              ->addForeignKey('category_id', 'categories', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();

        $data = [
            ['name' => 'Для смартфонов', 'category_id' => 1],
            ['name' => 'Для фото и видео', 'category_id' => 1],
            ['name' => 'Для ноутбуков', 'category_id' => 1],
            ['name' => 'Для наушников', 'category_id' => 1],

            ['name' => 'Apple', 'category_id' => 2],
            ['name' => 'Samsung', 'category_id' => 2],
            ['name' => 'Xiaomi', 'category_id' => 2],
            ['name' => 'Poco', 'category_id' => 2],
            ['name' => 'Vivo', 'category_id' => 2],
            ['name' => 'Realmi', 'category_id' => 2],
            ['name' => 'Huawei', 'category_id' => 2],
            ['name' => 'Asus', 'category_id' => 2],
            ['name' => 'Honor', 'category_id' => 2],

            ['name' => 'Apple', 'category_id' => 3],
            ['name' => 'Samsung', 'category_id' => 3],
            ['name' => 'Xiaomi', 'category_id' => 3],
            ['name' => 'Lenovo', 'category_id' => 3],
            ['name' => 'Hp', 'category_id' => 3],
            ['name' => 'Realmi', 'category_id' => 3],
            ['name' => 'Huawei', 'category_id' => 3],
            ['name' => 'Honor', 'category_id' => 3],

            ['name' => 'Безпроводные наушники', 'category_id' => 4],
            ['name' => 'Колонки', 'category_id' => 4],
            ['name' => 'Проводные наушники', 'category_id' => 4],

            ['name' => 'Garmin', 'category_id' => 5],
            ['name' => 'Honor', 'category_id' => 5],
            ['name' => 'Xiaomi', 'category_id' => 5],
            ['name' => 'Apple', 'category_id' => 5],

            ['name' => 'Яндекс Станции', 'category_id' => 6],
            ['name' => 'Игровые приставки', 'category_id' => 6],
            ['name' => 'PowerPank', 'category_id' => 6],

            ['name' => 'Электросамокаты', 'category_id' => 7],
            
            ['name' => 'Apple', 'category_id' => 8],
            ['name' => 'Hp', 'category_id' => 8],
            ['name' => 'Asus', 'category_id' => 8],
            ['name' => 'Lenovo', 'category_id' => 8],
            ['name' => 'Xiaomi', 'category_id' => 8],
        ];
     
        $table->insert($data)->save();
    }
}
