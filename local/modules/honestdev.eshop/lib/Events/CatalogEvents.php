<?php
namespace Honestdev\Eshop\Events;


use Bitrix\Main\EventManager;
use CIBlockProperty;
use CSite;
use Honestdev\Eshop\Tools\Utils;

/**
 * Class CatalogEvents
 * @package Honestdev\Eshop\Events
 */
class CatalogEvents extends Events
{
    public static function setUpHandlers()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementUpdate', [
            __CLASS__,
            'beforeElementUpdateHandler',
        ]);
    }

    public static function beforeElementUpdateHandler(&$arFields)
    {

        $propertyOldPriceId = CIBlockProperty::GetList([],["IBLOCK_ID" => $arFields['IBLOCK_ID'],"CODE" => 'OLD_PRICE'])->GetNext()['ID'];
        $propertySaleSizeId = CIBlockProperty::GetList([],["IBLOCK_ID" => $arFields['IBLOCK_ID'],"CODE" => 'SALE_SIZE'])->GetNext()['ID'];

        if (empty($propertyOldPriceId) || empty($propertySaleSizeId)) {
            return true;
        }

        $oldPrice = reset($arFields['PROPERTY_VALUES'][$propertyOldPriceId])['VALUE'];

        $currentPrice = \Bitrix\Catalog\Model\Price::getList(
            [
                'filter' => ['PRODUCT_ID' => $arFields['ID']],
                'select' => ['PRICE']
            ]
        )->fetch()['PRICE'];

        if (!empty($oldPrice)) {
            $arFields['PROPERTY_VALUES'][$propertySaleSizeId][array_key_first($arFields['PROPERTY_VALUES'][$propertySaleSizeId])]['VALUE'] = (int)$oldPrice - (int)$currentPrice;
        } else {
            $arFields['PROPERTY_VALUES'][$propertySaleSizeId] = "";
        }
    }
}