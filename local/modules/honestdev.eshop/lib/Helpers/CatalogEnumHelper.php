<?php


namespace Honestdev\Eshop\Helpers;

use Honestdev\Eshop\Tools\Utils;

class CatalogEnumHelper extends IblockFieldEnumHelper
{

    protected static $enumBooleanFields = [
        'TYPE',
    ];

    public static function getIblockId()
    {
        return Utils::getIblockIdByCode(HDESHOP_CATALOG_IBLOCK_CODE);
    }

    public static function getFieldIdByFieldCode(string $fieldCode, ?string $entityId = null): ?int
    {
        return parent::getFieldIdByFieldCode($fieldCode, self::getIblockId());
    }

    public static function getListByFieldCode(string $fieldCode, ?string $entityId = null): array
    {
        return parent::getListByFieldCode($fieldCode, self::getIblockId());
    }

    public static function getXmlIdsByFieldCode(string $fieldCode, ?string $entityId = null): array
    {
        return parent::getXmlIdsByFieldCode($fieldCode, self::getIblockId());
    }

    public static function getFieldByCode(string $fieldCode, ?string $entityId = null): ?array
    {
        return parent::getFieldByCode($fieldCode, self::getIblockId());
    }

    public static function getFieldsEnumValuesByFieldsCodes(array $fieldCodes, ?string $entityId = null): array
    {
        return parent::getFieldsEnumValuesByFieldsCodes($fieldCodes, self::getIblockId());
    }

    public static function getFieldsIdsByFieldsCodes(array $fieldCodes, ?string $entityId = null): array
    {
        return parent::getFieldsIdsByFieldsCodes($fieldCodes, self::getIblockId());
    }

    public static function getValueIdByXmlId(string $xmlId, string $fieldCode): ?int
    {
        $arEnumField = static::getByXmlId($xmlId, $fieldCode) ?: [];
        return (int)$arEnumField['ID'] ?: null;
    }
}