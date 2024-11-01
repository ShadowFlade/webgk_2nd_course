<?php

namespace Webgk\Service\CatalogSync;

use Webgk\Service\CatalogSync\Logger;

//supplementary class only for testing, does not affect the synchronization in any way
class Utils
{
    public static function createSections($ibOut)
    {
        $logger = new Logger();
        $sections = [
            [
                'CODE' => 'shoes',
                'NAME' => 'Обувь'
            ],
            [
                'CODE' => 'dresses',
                'NAME' => 'Платья',
            ],
            [
                'CODE' => 'pants',
                'NAME' => 'Штаны',
            ],
            [
                'CODE' => 'underwear',
                'NAME' => 'Нижнее белье',
            ],
            [
                'CODE' => 't-shirts',
                'NAME' => 'Футболки',
            ],
            [
                'CODE' => 'sportswear',
                'NAME' => 'Спортивная Одежда',
            ],
            [
                'CODE' => 'accessories',
                'NAME' => 'Аксессуары',
            ],
            [
                'CODE' => 'new_products',
                'NAME' => 'Новые продукты',
            ],
        ];
        $newIds = [];
        foreach ($sections as $section) {

            $newSection = new \CIBlockSection();
            $addRes = $newSection->Add(
                array_merge(
                    $section,
                    ['ACTIVE' => 'Y', 'IBLOCK_ID' => $ibOut]
                ),
                false
            );


            if (!empty($addRes)) {
                $id = $addRes;
                $newIds[$section['CODE']] = $id;
            } else {
                $logger->logError("Could not add section with code {$section['CODE']} in {$section['NAME']}.']}");
            }
            if (!empty($newIds)) {
                $logger->logProcess(['newly created section ids' => $newIds]);
            }
        }

        return $newIds;
    }
}