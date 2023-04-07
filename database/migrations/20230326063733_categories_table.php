<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CategoriesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('categories');
        $table->addColumn('name', 'string', ['limit' => 55, 'null' => FALSE, 'collation' => 'utf8_general_ci'])->create();
    
        $data = [
            ['name' => 'Аксессуары'],
            ['name' => 'Смартфоны'],
            ['name' => 'Планшеты'],
            ['name' => 'Наушники и колонки'],
            ['name' => 'Часы'],
            ['name' => 'Гаджеты'],
            ['name' => 'Электросамокаты'],
            ['name' => 'Ноутбуки'],
        ];

        $table->insert($data)->save();
    }
}
