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

        /* Записи в качестве примера для тестирования,
        при ненадомности можно удалить последующий код */
        
        $data = [
            ['name' => 'Производитель', 'value' => 'Xiaomi', 'product_id' => 1],
            ['name' => 'Диагональ', 'value' => '6.55 дюйм', 'product_id' => 1],
            ['name' => 'Процессор', 'value' => 'Qualcomm SM7450-AB Snapdragon 7 Gen 1 (4 nm)', 'product_id' => 1],
            ['name' => 'Основная камера', 'value' => '50 MP + 8 MP + 2 MP', 'product_id' => 1],
            
            ['name' => 'Производитель', 'value' => 'Honor', 'product_id' => 2],
            ['name' => 'Процессор', 'value' => 'Kirin A1', 'product_id' => 2],
            ['name' => 'Динамик', 'value' => '+', 'product_id' => 2],

            ['name' => 'Производитель', 'value' => 'Apple', 'product_id' => 3],
            ['name' => 'Операционная система', 'value' => 'iOS 12', 'product_id' => 3],
            ['name' => 'Процессор', 'value' => 'Apple A12 Bionic', 'product_id' => 3],
            ['name' => 'Фронтальная камера', 'value' => '7 MP', 'product_id' => 3],
            ['name' => 'Частота кадров видеосъемки	', 'value' => '60 кадр / сек', 'product_id' => 3],

            ['name' => 'Производитель', 'value' => 'Honor', 'product_id' => 4],
            ['name' => 'Операционная система', 'value' => 'Android 9.0 Pie', 'product_id' => 4],
            ['name' => 'Процессор', 'value' => '8-Core MediaTek Helio P35 MT6765', 'product_id' => 4],
            ['name' => 'Основная камера', 'value' => '13 MP', 'product_id' => 4],
            ['name' => 'Фронтальная камера', 'value' => '8 MP', 'product_id' => 4],

            ['name' => 'Емкость аккум. (мА·ч)', 'value' => '180', 'product_id' => 5],
            ['name' => 'Материал корпуса', 'value' => 'Пластик/силикон', 'product_id' => 5],
            ['name' => 'Bluetooth', 'value' => '5.2', 'product_id' => 5],
            ['name' => 'Вес (г)', 'value' => '13.5', 'product_id' => 5],

            ['name' => 'Время автономной работы', 'value' => '5 ч', 'product_id' => 6],
            ['name' => 'Емкость аккум. (мА·ч)', 'value' => '30', 'product_id' => 6],
            ['name' => 'Вид наушников', 'value' => 'внутриканальные', 'product_id' => 6],
            ['name' => 'Тип наушников', 'value' => 'динамические', 'product_id' => 6],
            ['name' => 'Разъем для зарядки', 'value' => 'Type-C', 'product_id' => 6],
            ['name' => 'Защита от воды', 'value' => '+', 'product_id' => 6],

            ['name' => 'Модель', 'value' => 'L05C', 'product_id' => 7],
            ['name' => 'Источник питания', 'value' => 'От сети', 'product_id' => 7],
            ['name' => 'Тип подключения', 'value' => 'Bluetooth 5.0, Wi-Fi', 'product_id' => 7],
            ['name' => 'Вес', 'value' => '628 г', 'product_id' => 7],
            ['name' => 'Размер', 'value' => '95 x 95 x 140 мм', 'product_id' => 7],

            ['name' => 'Модель', 'value' => 'SKU: 4191', 'product_id' => 8],
            ['name' => 'Производитель', 'value' => 'Xiaomi', 'product_id' => 8],
            ['name' => 'Вес (g)', 'value' => '2000', 'product_id' => 8],
            ['name' => 'Размеры', 'value' => '360.7 x 243.6 x 16.9 мм', 'product_id' => 8],
            ['name' => 'Материал', 'value' => 'Металл', 'product_id' => 8],
            ['name' => 'Объем оперативной памяти', 'value' => '16 ГБ', 'product_id' => 8],
            ['name' => 'Количество ядер процессора', 'value' => '4', 'product_id' => 8],
            ['name' => 'Процессор', 'value' => 'Intel Core i7', 'product_id' => 8],
            ['name' => 'Видеокарта', 'value' => 'NVIDIA GeForce MX250', 'product_id' => 8],

            ['name' => 'Производитель', 'value' => 'Яндекс', 'product_id' => 9],
            ['name' => 'Bluetooth', 'value' => '5.0', 'product_id' => 9],
            ['name' => 'Размер', 'value' => '123 х 199 х 99 мм', 'product_id' => 9],
            ['name' => 'Аккумулятор', 'value' => 'От сети', 'product_id' => 9],
        ];   

        $table->insert($data)->save();
    }
}
