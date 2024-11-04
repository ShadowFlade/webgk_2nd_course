<?php

namespace Webgk\Restriction\Sale\Payment;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Services\Base\Restriction;


class paymentrestriction extends Restriction
{
    public static function getClassTitle()
    {

        return 'По группе пользователей';
    }

    public static function getClassDescription()
    {

        return 'Делаем доступным данную оплату только определенной группе';
    }

    /**
     * сравниваем выбранный регион с регионами, указанными в доставке
     *
     * @param mixed $location
     * @param mixed $restrictionParams
     * @param mixed $paysystemId
     */
    public static function check($userGroupArray, array $restrictionParams, $paysystemId = 0)
    {

        $partnersGroupId = static::getUserGroupPartnersId();
        if (intval($paysystemId) <= 0)
            return true;
        $is = in_array($partnersGroupId, $userGroupArray);

        return $is;
    }

    private static function getUserGroupPartnersId()
    {

        $groupRes = \Bitrix\Main\GroupTable::getList([
            'select' => ['NAME', 'ID', 'STRING_ID', 'C_SORT'],
            'filter' => ['STRING_ID' => 'PARTNERS']
        ])->fetch();
        if (!empty($groupRes)) {
            $partnersGroupId = $groupRes['ID'];
        } else {
            return 0;
        }
        return $partnersGroupId;
    }

    private static function getUserGroups()
    {
        $groupResDB = \Bitrix\Main\GroupTable::getList([
            'select' => ['NAME', 'ID', 'STRING_ID', 'C_SORT'],
        ]);

        while ($group = $groupResDB->fetch()) {
            $groups[$group['ID']] = $group['NAME'];
        }

        return $groups;
    }

    /**
     * получаем группу пользовтеля
     *
     * @param Entity $entity
     */
    protected static function extractParams(Entity $entity)
    {

        global $USER;
        $userGroup = $USER->GetUserGroupArray();
        return $userGroup;
    }


    /**
     * описание ограничения
     *
     * @param mixed $entityId
     */
    public static function getParamsStructure($entityId = 0)
    {

        return array(
            "GROUP_IDS" => array(
                "TYPE" => "ENUM",
                "LABEL" => "Группы пользователей",
                "OPTIONS" => static::getUserGroups()
            )

        );
    }

}