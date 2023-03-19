<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CharacteristicsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('characteristics');
        $table->addColumn('name', 'string', ['limit' => 255, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('value', 'string', ['limit' => 255, 'null' => FALSE, 'collation' => 'utf8_general_ci'])
              ->addColumn('product_id', 'integer', ['limit' => 11, 'null' => FALSE, 'signed' => false])
              ->addIndex(['name', 'product_id'])
              ->addForeignKey('product_id', 'products', 'id',  ['update' => 'CASCADE', 'delete' => 'CASCADE'])
              ->create();
    }
}
