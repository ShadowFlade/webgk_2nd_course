<?

use Bitrix\Main\Loader,
    Bitrix\Main,
    Bitrix\Iblock,
    Ibrush\Main\Helpers\HighloadBlock as HL;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/interface/admin_lib.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$import1CList = \Webgk\Singletons\Exchange1C::get1CImportList();
//echo '<pre>';
//print_r($import1CList);
//echo '</pre>';
?>
    <ul>
        <? foreach ($import1CList as $directory): ?>
            <li>
                <div style="display:flex;align-items:center"><h4><?= $directory['exchangeName'] ?></h4>
                    <span style="margin-left:3rem"> Дата выгрузки <?= $directory['exchage_date_formatted'] ?></span>
                </div>

                <ul>
                    <? foreach ($directory['files'] as $file): ?>
                        <li>
                            <a download href="<?= $file['path'] ?>"><?= $file['name'] ?></a>
                        </li>
                    <? endforeach ?>
                </ul>
            </li>
        <? endforeach ?>
    </ul>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>