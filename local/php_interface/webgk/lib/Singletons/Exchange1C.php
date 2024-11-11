<?php

namespace Webgk\Singletons;
class Exchange1C
{
    public static function get1CImportList()
    {

        $root = $_SERVER['DOCUMENT_ROOT'];

        $list = [];
        $map = [
            'import' => 'Импорт',
            'offers' => 'Торговые предложения',
            'references' => 'Пользовательские справочники',
            'rests' => 'Остатки',
            'prices' => 'Цены'
        ];
        if ($handle = opendir($root . '/upload')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry == "." && $entry == ".." && empty($entry)) {
                    continue;
                }
                if (!str_contains($entry, "1c_catalog")) {
                    continue;
                }
                $files = array_filter(scandir($root . '/upload/' . $entry), fn($file) => $file != '.' && $file != '..' && !empty($file));
                $importNumber = null;
                preg_match('/\d$/', $entry, $importNumber);
                $changeTime = filectime($root . '/upload/' . $entry);
                $changeTimeFormatted = date('Y-m-d H:i:s', $changeTime);
                foreach ($map as $proposedFileName => $locFileName) {
                    $fileName = '';

                    if (str_contains($locFileName, $proposedFileName)) {
                        $fileName = $locFileName;
                    }
                }
                $list[] = [
                    'exchangeName' => 'Выгрузка ' . $importNumber[0] ?: '',
                    'exchange_date' => $changeTime,
                    'exchage_date_formatted' => $changeTimeFormatted,
                    'files' => array_map(fn($item) => [
                        'path' => 'https://' . $_SERVER['HTTP_HOST'] . '/upload/' . $entry . '/' . $item,
                        'name' => self::getFileNamePublicView(basename($item), $map),
                    ], $files)
                ];
            }
            closedir($handle);
        }
        usort($list, fn($item1, $item2) => $item1['exchange_date'] < $item2['exchange_date']);
        return $list;

    }

    private static function getFileNamePublicView($filename, $map)
    {
        $resFilename = '';
        foreach ($map as $proposedFileName => $locFileName) {
            if (str_contains($filename, $proposedFileName)) {
                $resFilename = $locFileName;
                break;
            }
        }
        return $resFilename;
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