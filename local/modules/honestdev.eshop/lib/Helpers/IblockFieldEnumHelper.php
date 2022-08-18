<?php

namespace Honestdev\Eshop\Helpers;

use Bitrix\Main\UserFieldTable;
use CIBlockProperty;
use CUserFieldEnum;

use InvalidArgumentException;

class IblockFieldEnumHelper
{
    const YES_XML_ID = 'Y';
    const NO_XML_ID = 'N';
    const MALE_XML_ID = 'M';
    const FEMALE_XML_ID = 'F';

    protected static $enumBooleanFields = [
        'ACTIVE'
    ];

    public static function getEnumBooleanFields(): array
    {
        return static::$enumBooleanFields;
    }

    public static function isEnumBooleanField(string $fieldCode): bool
    {
        return in_array($fieldCode, static::getEnumBooleanFields());
    }

    public static function isYes(int $valueId): bool
    {
        return static::getXmlIdByValueId($valueId) == self::YES_XML_ID;
    }

    public static function isNo(int $valueId): bool
    {
        return static::getXmlIdByValueId($valueId) == self::NO_XML_ID;
    }

    public static function isMale(int $valueId): bool
    {
        return static::getXmlIdByValueId($valueId) == self::MALE_XML_ID;
    }

    public static function isFemale(int $valueId): bool
    {
        return static::getXmlIdByValueId($valueId) == self::FEMALE_XML_ID;
    }

    public static function getFieldByCode(string $fieldCode, ?int $ibclockId = null): ?array
    {
        if (!empty($ibclockId)) {
            $arFilter['IBLOCK_ID'] = $ibclockId;
            $arFilter['CODE'] = $fieldCode;
        }

        return CIBlockProperty::GetList(
            [],
            $arFilter
        )->Fetch() ?: null;
    }

    public static function getFieldIdByFieldCode(string $fieldCode, ?string $iblockId = null): ?int
    {
        $arField = static::getFieldByCode($fieldCode, $iblockId);
        return !empty($arField) ? $arField['ID'] : null;
    }

    public static function getFieldsIdsByFieldsCodes(array $fieldCodes, ?string $entityId = null): array
    {
        $arFilter = ['FIELD_NAME' => $fieldCodes];
        if (!empty($entityId)) {
            $arFilter['ENTITY_ID'] = $entityId;
        }

        $rsField = UserFieldTable::getList([
                                               'filter' => $arFilter,
                                               'select' => ['ID', 'FIELD_NAME']
                                           ]);

        $fieldsIds = [];
        while ($arField = $rsField->fetch()) {
            $fieldsIds[$arField['FIELD_NAME']] = $arField['ID'];
        }

        return $fieldsIds;
    }

    public static function getListByFieldCode(string $fieldCode, ?string $entityId = null): array
    {
        $fieldId = static::getFieldIdByFieldCode($fieldCode, $entityId);
        if (empty($fieldId)) {
            throw new InvalidArgumentException('Не найдено пользовательского свойства с кодом: ' . $fieldCode);
        }

        $rsEnumField = CUserFieldEnum::GetList(['SORT' => 'DESC', 'NAME' => 'DESC'], ['USER_FIELD_ID' => $fieldId]);
        while ($arEnumField = $rsEnumField->Fetch()) {
            if (!empty($arEnumField['ID'])) {
                $arEnumFieldList[$arEnumField['ID']] = $arEnumField;
            }
        }

        return !empty($arEnumFieldList) ? $arEnumFieldList : [];
    }

    public static function getXmlIdsByFieldCode(string $fieldCode, ?string $entityId = null): array
    {
        foreach (static::getListByFieldCode($fieldCode, $entityId) as $fieldId => $arField) {
            if (!empty($arField['XML_ID'])) {
                $arXmlIds[$fieldId] = $arField['XML_ID'];
            }
        }
        return !empty($arXmlIds) ? $arXmlIds : [];
    }

    public static function getXmlIdByValueId(int $valueId): ?string
    {
        $xmlId = (string)CUserFieldEnum::GetList([], ['ID' => $valueId])->Fetch()['XML_ID'];
        return $xmlId ?: null;
    }

    public static function getValueByValueId(int $valueId,string $fieldCode): ?string
    {
        $value = (string)CIBlockProperty::GetPropertyEnum(
            $fieldCode,
            [],
            ["ID" =>$valueId]
        )->fetch()['VALUE'];
        return $value ?: null;
    }

    public static function getByXmlId(string $xmlId, string $fieldCode): ?array
    {
        $fieldId = static::getFieldIdByFieldCode($fieldCode);

        if (empty($fieldId)) {
            throw new InvalidArgumentException('Не найдено пользовательского свойства с кодом: ' . $fieldCode);
        }

        $arEnumField = CIBlockProperty::GetPropertyEnum(
            $fieldId,
            [],
            ["XML_ID" =>$xmlId]
        )->fetch();

        return !empty($arEnumField) ? $arEnumField : null;
    }

    public static function getValueIdByXmlId(string $xmlId, string $fieldCode): ?int
    {
        $arEnumField = static::getByXmlId($xmlId, $fieldCode) ?: [];
        return (int)$arEnumField['ID'] ?: null;
    }

    public static function getYesValueId(string $fieldCode): ?int
    {
        return static::getValueIdByXmlId(self::YES_XML_ID, $fieldCode);
    }

    public static function getNoValueId(string $fieldCode): ?int
    {
        return static::getValueIdByXmlId(self::NO_XML_ID, $fieldCode);
    }

    public static function getMaleValueId(string $fieldCode): ?int
    {
        return static::getValueIdByXmlId(self::MALE_XML_ID, $fieldCode);
    }

    public static function getFemaleValueId(string $fieldCode): ?int
    {
        return static::getValueIdByXmlId(self::FEMALE_XML_ID, $fieldCode);
    }

    public static function getGenderCodeByValueId(int $valueId): ?string
    {
        if(static::isMale($valueId)) {
            return static::MALE_XML_ID;
        }

        if (static::isFemale($valueId)) {
            return static::FEMALE_XML_ID;
        }

        return null;
    }

    public static function getBooleanValueByValueId(int $valueId): ?bool
    {
        if(static::isYes($valueId)) {
            return true;
        }

        if (static::isNo($valueId)) {
            return false;
        }

        return null;
    }

    public static function getValueIdByGenderCode(?string $genderCode, string $fieldCode): ?string
    {
        if(!is_null($genderCode)) {
            if($genderCode == static::MALE_XML_ID) {
                return static::getMaleValueId($fieldCode);
            }

            if($genderCode == static::FEMALE_XML_ID) {
                return static::getFemaleValueId($fieldCode);
            }
        }

        return null;
    }

    public static function getValueIdByBooleanValue(?bool $booleanValue, string $fieldCode): ?string
    {
        if(!is_null($booleanValue)) {
            return $booleanValue == true ? static::getYesValueId($fieldCode) : static::getNoValueId($fieldCode);
        }

        return null;
    }

    public static function getFieldsEnumValuesByFieldsCodes(array $fieldCodes, ?string $entityId = null): array
    {
        if (empty($fieldsIds = static::getFieldsIdsByFieldsCodes($fieldCodes, $entityId))) {
            return [];
        }

        $fieldsCodes = array_flip($fieldsIds);
        $rsEnumField = CUserFieldEnum::GetList(['SORT' => 'DESC', 'NAME' => 'DESC'], ['USER_FIELD_ID' => $fieldsIds]);

        $arFieldsEnumValues = [];
        while ($arEnumField = $rsEnumField->Fetch()) {
            if ($fieldCode = $fieldsCodes[$arEnumField['USER_FIELD_ID']]) {
                $arFieldsEnumValues[$fieldCode][$arEnumField['ID']] = $arEnumField['VALUE'];
            }
        }

        return $arFieldsEnumValues;
    }

}