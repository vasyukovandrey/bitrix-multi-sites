<?php

namespace Honestdev\Eshop\Service;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Honestdev\Eshop\Tools\Utils;

class CatalogService
{
    public static function getIblockId()
    {
        return Utils::getIblockIdByCode(HDESHOP_CATALOG_IBLOCK_CODE);
    }

    public static function getElementIdsByBrandCode(string $prefilter)
    {
        $elementIds = [];
        $arElements = \CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID" => self::getIblockId(),
                'PROPERTY_BRAND_VALUE' => $prefilter
            ],
            false,
            [],
            ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID']
        );

        while ($element = $arElements->Fetch()) {
            $elementIds[] = $element['ID'];
        }

        return $elementIds;
    }

    public static function getSectionIdsByElementIds(array $elementIds)
    {
        if (empty($elementIds)) {
            return [];
        }

        $sectionIds = [];

        $sectionElements = ElementTable::getList(
            [
                'filter' => [
                    'ID' => $elementIds
                ],
                'select' => [
                    'ID',
                    'SECTION_ELEMENT_ID' => 'SECTION_ELEMENT.IBLOCK_SECTION_ID',
                ],
                'runtime' => [
                    new \Bitrix\Main\ORM\Fields\Relations\Reference(
                        'SECTION_ELEMENT',
                        \Bitrix\Iblock\SectionElementTable::class,
                        \Bitrix\Main\Entity\Query\Join::on(
                            'this.ID',
                            'ref.IBLOCK_ELEMENT_ID'
                        )
                    )
                ],
            ]
        )->fetchAll();

        foreach ($sectionElements as $item) {
            $sectionIds[$item['SECTION_ELEMENT_ID']] = $item['SECTION_ELEMENT_ID'];
        }

        return $sectionIds;
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getTreeSectionPrefilter(string $prefilter): array
    {
        if (empty($prefilter)) {
            return [];
        }

        $sectionIds = self::getSectionIdsByElementIds(self::getElementIdsByBrandCode($prefilter));

        return ['ID' => Utils::getSectionTreeIdsByIds($sectionIds)];
    }

    public static function getBrandCodeById(int $brandId): string
    {
        $brandCode = '';

        if (empty($brandId)) {
            return $brandCode;
        }

        $arElements = \CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID" => Utils::getIblockIdByCode(HDESHOP_BRANDS_IBLOCK_CODE),
                "ID" => $brandId
            ],
            false,
            [],
            ['ID', 'CODE']
        );

        if ($element = $arElements->Fetch()) {
            $brandCode = $element['CODE'];
        }

        return $brandCode;
    }
}