<?php
namespace Webgk\Service\CatalogSync;
class Utils
{
    public static function createSections($ibOut)
    {
        $sections = [
            [
                'code' => 'shoes',
                'name' => 'Обувь'
            ],
            [
                'code' => 'dresses',
                'name' => 'Платья',
            ],
            [
                'code' => 'pants',
                'name' => 'Штаны',
            ],
            [
                'code' => 'underwear',
                'name' => 'Нижнее белье',
            ],
            [
                'code' => 't-shirts',
                'name' => 'Футболки',
            ],
            [
                'code' => 'sportswear',
                'name' => 'Спортивная Одежда',
            ],
            [
                'code' => 'accessories',
                'name' => 'Аксессуары',
            ],
            [
                'code' => 'new_products',
                'name' => 'Новые продукты',
            ],
        ];
        foreach ($sections as $section) {
            \Bitrix\Iblock\SectionTable::add(array_merge($section,['IBLOCK_ID'=>$ibOut]));
        }
    }
}