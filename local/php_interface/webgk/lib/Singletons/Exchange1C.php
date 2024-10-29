<?php

namespace Webgk\Singletons;
class Exchange1C
{
    public static function get1CImportList()
    {

        $root = $_SERVER['DOCUMENT_ROOT'];

        $list = [];
        $map = [
            'import.xml' => 'Импорт',
            'offers.xml' => 'Торговые предложения',
            'references.xml' => 'Пользовательские справочники',
            'rests.xml' => 'Остатки',
            'prices.xml' => 'Цены'
        ];
        if ($handle = opendir($root . '/upload')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry == "." && $entry == "..") {
                    continue;
                }
                if (!str_contains($entry, "1c_catalog")) {
                    continue;
                }
                $files = array_filter(scandir($root . '/upload/' . $entry), fn($file) => $file != '.' && $file != '..');
                $importNumber = null;
                preg_match('/\d$/', $entry, $importNumber);
                $changeTime = filectime($root . '/upload/' . $entry);
                $changeTimeFormatted = date('Y-m-d H:i:s', $changeTime);

                $list[] = [
                    'exchangeName' => 'Выгрузка ' . $importNumber[0] ?: '',
                    'exchange_date' => $changeTime,
                    'exchage_date_formatted' => $changeTimeFormatted,
                    'files' => array_map(fn($item) => [
                        'path' => $root . '/upload/' . $entry . '/' . $item,
                        'name' => $map[basename($item)],
                    ], $files
                    )
                ];
            }
            closedir($handle);
        }
        usort($list, fn($item1, $item2) => $item1['exchange_date'] < $item2['exchange_date']);
        return $list;

    }

    public static function ModifyAdminMenu(&$adminMenu, &$moduleMenu)
    {
        $moduleMenu[] = [
            "parent_menu" => "global_menu_services", // в раздел "Сервис"
            "section" => "Выгрузки 1С",
            "sort" => 11,                    // сортировка пункта меню - поднимем повыше
            "url" => "/bitrix/admin/exchange_1c.php",  // ссылка на пункте меню
            "text" => 'Выгрузки 1С',
            "title" => 'Выгрузки 1С',
            "icon" => "smile_menu_icon", // малая иконка
            "page_icon" => "smile_menu_icon", // большая иконка
            "items_id" => "menu_testpagelex",  // идентификатор ветви
            "items" => []          // остальные уровни меню
        ];
    }

}