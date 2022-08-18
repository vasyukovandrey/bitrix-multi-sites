<?php

namespace Honestdev\Eshop\Service;

use Bitrix\Main\Localization\CultureTable;
use CFile;
use CIBlock;
use CIBlockProperty;
use CIBlockPropertyEnum;
use CModule;
use CSite;
use Honestdev\Eshop\Helpers\CatalogEnumHelper;
use Honestdev\Eshop\Model\MonoSiteModel;
use Honestdev\Eshop\Repository\MonoSitesRepository;
use Honestdev\Eshop\Tools\Utils;

class MonoSitesService
{
    const CATEGORY_TYPE = 1;
    const BRAND_TYPE = 2;

    const BASE_TEMPLATE_FOLDER = "/local/templates";
    const BLANK_TEMPLATE_FOLDER = "/blank/";

    public static array $globalIblocks = [
        //HDESHOP_CATALOG_IBLOCK_CODE,
        //HDESHOP_BRANDS_IBLOCK_CODE,
        //HDESHOP_PRODUCT_SETS_IBLOCK_CODE,
        //HDESHOP_REVIEWS_IBLOCK_CODE
    ];

    public static array $monoSiteIblocks = [
        HDESHOP_SLIDER_IBLOCK_CODE,
        HDESHOP_SALE_IBLOCK_CODE,
        HDESHOP_NEWS_IBLOCK_CODE,
        HDESHOP_BLOG_IBLOCK_CODE,
        HDESHOP_GALLERY_IBLOCK_CODE,
        HDESHOP_CATALOG_IBLOCK_CODE,
        HDESHOP_BRANDS_IBLOCK_CODE,
        HDESHOP_PRODUCT_SETS_IBLOCK_CODE,
        HDESHOP_REVIEWS_IBLOCK_CODE
    ];

    private static array $monoSiteFolders = [
        '/auth/',
        '/blog/',
        '/bonusnye-programmy/',
        '/brendy/',
        '/catalog/',
        '/dostavka-i-oplata/',
        '/kontakty/',
        '/login/',
        '/novosti/',
        '/o-nas/',
        '/otzyvy/',
        '/personal/',
        '/sale/',
        '/search/',
        '/vozvrat/',
    ];

    private static array $monoSiteFiles = [
        '.access.php',
        '.bottom.menu.php',
        '.catalog.menu.php',
        '.catalog.menu_ext.php',
        '.footer.menu.php',
        '.footer_catalog.menu.php',
        '.footer_catalog.menu_ext.php',
        '.htaccess',
        '.left.menu_ext.php',
        '.personal.menu.php',
        '.section.php',
        '.supermenu_sub.menu.php',
        '.top.menu.php',
        '404.php',
        'favicon.svg',
        'index.php',
        'urlrewrite.php',
    ];

    private array $propsMapping = [];
    private array $processedElementsCodes = [];

    private MonoSiteModel $monoSite;
    private string $siteId;

    public function __construct(MonoSiteModel $monoSite)
    {
        $this->monoSite = $monoSite;
        CModule::IncludeModule("iblock");
    }

    public static function checkIsMonoSite(): bool
    {
        return SITE_ID != 's1';
    }

    public static function getCurrentMonoSiteCode()
    {
        if (SITE_ID == 'ru') {
            return '';
        }

        return (new MonoSitesRepository)->getElement(['filter' => ['UF_SITE_ID' => SITE_ID]])->getCode();
    }

    public static function getCurrentMonoSite(): ?MonoSiteModel
    {
        return (new MonoSitesRepository())->getElement(['filter' => ['UF_SITE_ID' => SITE_ID]]);
    }

    /**
     * @throws \Exception
     */
    public static function getMonoSiteElementFilter(): array
    {
        $result = [];
        $curSite = self::getCurrentMonoSite();

        switch ($curSite->getType()){
            case self::BRAND_TYPE:
                $brandCode = CatalogService::getBrandCodeById((int)$curSite->getEntityId());
                $result = ['PROPERTY_BRAND' => CatalogEnumHelper::getValueIdByXmlId($brandCode, 'BRAND')];
                break;
            case self::CATEGORY_TYPE:
                $result = [];
                break;
        }

        return $result;
    }

    public static function getMonoSiteSectionFilter(): array
    {
        $result = [];
        $curSite = self::getCurrentMonoSite();

        switch ($curSite->getType()){
            case self::BRAND_TYPE:
                $brandCode = CatalogService::getBrandCodeById((int)$curSite->getEntityId());
                $sectionIds = CatalogService::getSectionIdsByElementIds(CatalogService::getElementIdsByBrandCode($brandCode));

                $result = ['ID' => Utils::getSectionTreeIdsByIds($sectionIds)];
                break;
            case self::CATEGORY_TYPE:
                $result = ['CATEGORY_ID' => (int)$curSite->getEntityId()];
                break;
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function copyIblocks(array $iblockCodes = [])
    {
        $ib = new CIBlock;
        $ibp = new CIBlockProperty;

        if (empty($iblockCodes)) {
            $iblockCodes = self::$monoSiteIblocks;
        }

        foreach ($iblockCodes as $iblockCode) {
            $iblockId = Utils::getIblockIdByCode($iblockCode, true , false);
            $arFields = CIBlock::GetArrayByID($iblockId);

            unset($arFields["ID"]);
            $arFields["GROUP_ID"] = CIBlock::GetGroupPermissions($iblockId);
            $arFields["NAME"] = $arFields["NAME"] . " (" . $this->monoSite->getName() . ')';
            $arFields['LID'] = $this->monoSite->getSiteId();
            $arFields['CODE'] = $arFields['CODE'] . '_' . $this->monoSite->getCode();
            $arFields['API_CODE'] = "";

            $newIblockId = $ib->Add($arFields);

            $properties = CIBlockProperty::GetList(
                ["sort" => "asc", "name" => "asc"],
                ["ACTIVE" => "Y", "IBLOCK_ID" => $iblockId]
            );

            while ($prop_fields = $properties->GetNext()) {
                if ($prop_fields["PROPERTY_TYPE"] == "L") {
                    $property_enums = CIBlockPropertyEnum::GetList(
                        ["DEF" => "DESC", "SORT" => "ASC"],
                        ["IBLOCK_ID" => $iblockId, "CODE" => $prop_fields["CODE"]]
                    );

                    while ($enum_fields = $property_enums->GetNext()) {
                        $prop_fields["VALUES"][] = [
                            "VALUE" => $enum_fields["VALUE"],
                            "DEF" => $enum_fields["DEF"],
                            "SORT" => $enum_fields["SORT"],
                            "XML_ID" => $enum_fields["XML_ID"],
                        ];
                    }
                }

                $prop_fields["IBLOCK_ID"] = $newIblockId;

                unset($prop_fields["ID"]);

                foreach ($prop_fields as $k => $v) {
                    if (!is_array($v)) {
                        $prop_fields[$k] = trim($v);
                    }
                    if (stripos($k, '~') !== false) {
                        unset($prop_fields[$k]);
                    }
                }

                $ibp->Add($prop_fields);
            }
        }
    }

    public function updateIblocks()
    {
        $ib = new CIBlock;
        $siteIds = [];
        $monoSites = (new MonoSitesRepository)->getElements(['select' => ['ID','UF_SITE_ID']], false);

        foreach ($monoSites as $monoSite) {
            $siteIds[] = $monoSite['UF_SITE_ID'];
        }

        foreach (self::$globalIblocks as $iblockCode) {
            $iblockId = Utils::getIblockIdByCode($iblockCode, true , false);
            $arFields = CIBlock::GetArrayByID($iblockId);
            $curLid = $arFields['LID'];

            if (!is_array($curLid)) {
                $curLid = [$curLid];
            }

            $arFields['LID'] = array_merge($curLid,$siteIds);
            $ib->Update($iblockId, $arFields);
        }
    }

    private function createSite()
    {
        $cultureId = CultureTable::getList(['filter'=>['CODE'=>'ru']])->fetch()['ID'];

        $arFields = [
            "LID" => $this->monoSite->getSiteId(),
            "ACTIVE" => "Y",
            "SORT" => trim($this->monoSite->getSiteId(), 's'),
            "DEF" => "N",
            "NAME" => $this->monoSite->getName(),
            "DIR" => "/",
            "FORMAT_DATE" => "DD.MM.YYYY",
            "FORMAT_DATETIME" => "DD.MM.YYYY HH:MI:SS",
            "CHARSET" => "UTF-8",
            "SITE_NAME" => $this->monoSite->getName(),
            "SERVER_NAME" => $this->monoSite->getDomain(),
            "EMAIL" => "sale@dev.technosad.ru",
            "LANGUAGE_ID" => "ru",
            "CULTURE_ID" => $cultureId,
            "DOC_ROOT" => "",
            "DOMAINS" => $this->monoSite->getDomain(),
            'TEMPLATE' => [
                [
                    "TEMPLATE" => $this->monoSite->getCode(),
                    "SORT" => 1,
                    "CONDITION" => ""
                ]
            ]
        ];

        $obSite = new CSite;
        $this->siteId = $obSite->Add($arFields);
    }

    private function copySiteFolders()
    {
        foreach (self::$monoSiteFolders as $folder) {
            Utils::copyFolder(
                $_SERVER["DOCUMENT_ROOT"].$folder,
                $_SERVER["DOCUMENT_ROOT"].'/../'.$this->monoSite->getDomain().$folder
            );
        }
    }

    private function copySiteFiles()
    {
        foreach (self::$monoSiteFiles as $file) {
            if(copy(
                $_SERVER["DOCUMENT_ROOT"].'/'.$file,
                 $_SERVER["DOCUMENT_ROOT"].'/../'.$this->monoSite->getDomain().'/'.$file
            )) {
                echo "Файл успешно скопирован!";
            }else{
                echo "Файл не удалось скопировать!";
            }
        }
    }

    private function copyTemplate()
    {
        Utils::copyFolder(
            $_SERVER["DOCUMENT_ROOT"].self::BASE_TEMPLATE_FOLDER.self::BLANK_TEMPLATE_FOLDER,
            $_SERVER["DOCUMENT_ROOT"].self::BASE_TEMPLATE_FOLDER.'/'.$this->monoSite->getCode().'/'
        );

        $text = "<?\$arTemplate = ['NAME' => 'Шаблон ".$this->monoSite->getName()."','DESCRIPTION' => 'Описание шаблона','SORT' => '','TYPE' => '',];?>";
        file_put_contents($_SERVER["DOCUMENT_ROOT"].self::BASE_TEMPLATE_FOLDER.'/'.$this->monoSite->getCode().'/description.php',$text);
    }

    private function getListPropsMapping(int $oldIblockId, int $newIblockId): void
    {
        $result = [];
        $properties = CIBlockProperty::GetList(
            ["sort" => "asc", "name" => "asc"],
            [
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $oldIblockId,
                "PROPERTY_TYPE" => "L"
            ]
        );

        while ($propFields = $properties->GetNext()) {
            $property_enums = CIBlockPropertyEnum::GetList(
                ["DEF" => "DESC", "SORT" => "ASC"],
                ["IBLOCK_ID" => $oldIblockId, "CODE" => $propFields["CODE"]]
            );
            while ($enum_fields = $property_enums->GetNext()) {
                $result[$propFields["CODE"]][$enum_fields["XML_ID"]]['oldId'] = $enum_fields["ID"];
            }
        }

        $properties = CIBlockProperty::GetList(
            ["sort" => "asc", "name" => "asc"],
            [
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $newIblockId,
                "PROPERTY_TYPE" => "L"
            ]
        );

        while ($propFields = $properties->GetNext()) {
            $property_enums = CIBlockPropertyEnum::GetList(
                ["DEF" => "DESC", "SORT" => "ASC"],
                ["IBLOCK_ID" => $newIblockId, "CODE" => $propFields["CODE"]]
            );
            while ($enum_fields = $property_enums->GetNext()) {
                $this->propsMapping[$propFields["CODE"]][$enum_fields["XML_ID"]]['newId'] = $enum_fields["ID"];
            }
        }
    }

    private function getSectionElements(int $iblockId, array $section = []) : array
    {
        $result = [];

        $filter = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y'
        ];

        if (!empty($section)) {
            $filter['SECTION_ID'] = $section['ID'];
        }

        $rs = \CIBlockElement::GetList(
            [
                'NAME' => 'asc'
            ],
            $filter,
            false,
            false,
            []
        );

        while ($item = $rs->GetNextElement()) {
            $arFields = $item->GetFields();
            $arFields['PROPERTIES'] = $item->GetProperties();

            $arFieldsCopy = [
                'NAME' => $arFields['NAME'],
                'CODE' => $arFields['CODE'],
                'ACTIVE' => $arFields['ACTIVE'],
                'IBLOCK_SECTION_ID' => $arFields['IBLOCK_SECTION_ID'],
                'SORT' => $arFields['SORT'],
                'PREVIEW_PICTURE' => CFile::MakeFileArray(CFile::GetPath($arFields['PREVIEW_PICTURE'])),
                'PREVIEW_TEXT' => $arFields['PREVIEW_TEXT'],
                'PREVIEW_TEXT_TYPE' => $arFields['PREVIEW_TEXT_TYPE'],
                'DETAIL_PICTURE' => $arFields['DETAIL_PICTURE'],
                'DETAIL_TEXT' => $arFields['DETAIL_TEXT'],
                'DETAIL_TEXT_TYPE' => $arFields['DETAIL_TEXT_TYPE'],
            ];
            foreach ($arFields['PROPERTIES'] as $property) {
                switch ($property['PROPERTY_TYPE']) {
                    case 'L':
                        if ($property['MULTIPLE'] == 'Y') {
                            $arFieldsCopy['PROPERTY_VALUES'][$property['CODE']] = [];

                            foreach ($property['VALUE_ENUM_ID'] as $enumID) {
                                $newEnumId =  $this->propsMapping[$property['CODE']][$property['VALUE_XML_ID']]['newId'];


                                if (empty($newEnumId)) {
                                    continue;
                                }

                                $arFieldsCopy['PROPERTY_VALUES'][$property['CODE']][] = ['VALUE' => $newEnumId];
                            }
                        } else {
                            $newEnumId =  $this->propsMapping[$property['CODE']][$property['VALUE_XML_ID']]['newId'];

                            if (empty($newEnumId)) {
                                continue;
                            }

                            $arFieldsCopy['PROPERTY_VALUES'][$property['CODE']] = ['VALUE' => $newEnumId];
                        }
                        break;
                    case 'F':
                        if ($property['MULTIPLE'] == 'Y') {
                            if (is_array($property['VALUE'])) {
                                foreach ($property['VALUE'] as $key => $arElEnum) {
                                    $arFieldsCopy['PROPERTY_VALUES'][$property['CODE']][$key] = CFile::CopyFile(
                                        $arElEnum
                                    );
                                }
                            }
                        } else {
                            $arFieldsCopy['PROPERTY_VALUES'][$property['CODE']] = CFile::CopyFile($property['VALUE']);
                        }
                        break;
                    default:
                        $arFields['PROPERTY_VALUES'][$property['CODE']] = $property['VALUE'];
                        break;
                }
            }
            $result[] = $arFieldsCopy;
        }

        return $result;
    }

    private function getChildSections(int $iblockId, array $section) {
        $result = [];
        $res = \CIBlockSection::GetList(
            [
                'NAME' => 'asc'
            ],
            [
                'IBLOCK_ID' => Utils::getIblockIdByCode(HDESHOP_CATALOG_IBLOCK_CODE),
                'DEPTH_LEVEL' => $section['DEPTH_LEVEL'] + 1,
                '>LEFT_MARGIN' => $section['LEFT_MARGIN'],
                '<RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
                'ACTIVE' => 'Y'
            ],
            false,
            [
                'ID',
                'IBLOCK_ID',
                'NAME',
                'CODE',
                'DESCRIPTION',
                'IBLOCK_SECTION_ID',
                'DEPTH_LEVEL',
                'LEFT_MARGIN',
                'RIGHT_MARGIN',
            ]
        );

        while($subsection = $res->fetch())
        {
            if (!empty($subsection['ID'])) {
                $copySubsection['SECTION'] = [
                    'ID' => $subsection['ID'],
                    'NAME' => $subsection['NAME'],
                    'CODE' => $subsection['CODE'],
                    'DEPTH_LEVEL' => $subsection['DEPTH_LEVEL'],
                    'LEFT_MARGIN' => $subsection['LEFT_MARGIN'],
                    'RIGHT_MARGIN' => $subsection['RIGHT_MARGIN'],
                    'PICTURE' => CFile::MakeFileArray(CFile::GetPath($subsection['PICTURE'])),
                    'DESCRIPTION' => $subsection['DESCRIPTION'],
                    'IBLOCK_SECTION_ID' => $subsection['IBLOCK_SECTION_ID'],
                ];

                $copySubsection['SUBSECTIONS'] = $this->getChildSections($iblockId, $copySubsection['SECTION']);
                $copySubsection['ELEMENTS'] = $this->getSectionElements($iblockId, $copySubsection['SECTION']);

                $result[] = $copySubsection;
            }
        }

        return $result;
    }

    private function getIblockSections(int $iblockId): array
    {
        $result = [];
        $res = \CIBlockSection::GetList(
            [
                'NAME' => 'asc'
            ],
            [
                'IBLOCK_ID' => Utils::getIblockIdByCode(HDESHOP_CATALOG_IBLOCK_CODE),
                'DEPTH_LEVEL' => 1,
                'ACTIVE' => 'Y'
            ],
            false,
            [
                'ID',
                'IBLOCK_ID',
                'NAME',
                'CODE',
                'PICTURE',
                'DESCRIPTION',
                'DESCRIPTION_TYPE',
                'IBLOCK_SECTION_ID',
                'DEPTH_LEVEL',
                'LEFT_MARGIN',
                'RIGHT_MARGIN',
            ]
        );

        while($section = $res->fetch())
        {
            $copySection['SECTION'] = [
                'ID' => $section['ID'],
                'NAME' => $section['NAME'],
                'CODE' => $section['CODE'],
                'DEPTH_LEVEL' => $section['DEPTH_LEVEL'],
                'LEFT_MARGIN' => $section['LEFT_MARGIN'],
                'RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
                'PICTURE' => CFile::MakeFileArray(CFile::GetPath($section['PICTURE'])),
                'DESCRIPTION' => $section['DESCRIPTION'],
                'DESCRIPTION_TYPE' => $section['DESCRIPTION_TYPE'],
                'IBLOCK_SECTION_ID' => $section['IBLOCK_SECTION_ID'],
            ];

            $copySection['SUBSECTIONS'] = $this->getChildSections($iblockId, $copySection['SECTION']);
            $copySection['ELEMENTS'] = $this->getSectionElements($iblockId, $copySection['SECTION']);

            $result[] = $copySection;
        }

        return $result;
    }

    private function copyCatalogSection(array $section, int $newIiblockId)
    {
        $iblockElements = new \CIBlockElement();
        $iblockSection = new \CIBlockSection;
        $section['SECTION']['IBLOCK_ID'] =  $newIiblockId;
        unset($section['SECTION']['ID']);
        $sectionId = $iblockSection->Add($section['SECTION']);

        if (!empty($section['SUBSECTIONS'])) {
            foreach ($section['SUBSECTIONS'] as $subsection) {
                $subsection['SECTION']['IBLOCK_SECTION_ID'] = $sectionId;
                $this->copyCatalogSection($subsection, $newIiblockId);
            }
        }

        if (!empty($section['ELEMENTS'])) {
            foreach ($section['ELEMENTS'] as $element) {
                if (!isset($this->processedElementsCodes[$element['CODE']])) {
                    $this->processedElementsCodes[$element['CODE']] = $element['CODE'];
                    $element['IBLOCK_SECTION_ID'] = $sectionId;
                    $element['IBLOCK_ID'] = $newIiblockId;
                    $iblockElements->Add($element);
                }
            }
        }
    }

    public function copyCatalog()
    {
        $iblockId = Utils::getIblockIdByCode(HDESHOP_CATALOG_IBLOCK_CODE, true , false);
        //$newIiblockId = Utils::getIblockIdByCode(HDESHOP_CATALOG_IBLOCK_CODE.'_'.$this->monoSite->getCode(), true , false);

        $newIiblockId = \CIBlock::GetList(
            ['ID' => 'ASC'],
            [
                "CHECK_PERMISSIONS" => "N",
                "ACTIVE" => "Y",
                "CODE" => HDESHOP_CATALOG_IBLOCK_CODE.'_'.$this->monoSite->getCode()
            ]
        )->Fetch()['ID'];

        $this->getListPropsMapping($iblockId,$newIiblockId);
        $catalogTree = self::getIblockSections($iblockId);

        foreach ($catalogTree as $section) {
            $this->copyCatalogSection($section, $newIiblockId);
        }
    }

    public function copyBrands()
    {
        $iblockElements = new \CIBlockElement();
        $iblockId = Utils::getIblockIdByCode(HDESHOP_BRANDS_IBLOCK_CODE, true , false);
        //$newIiblockId = Utils::getIblockIdByCode(HDESHOP_BRANDS_IBLOCK_CODE.'_'.$this->monoSite->getCode(), true , false);

        $newIiblockId = \CIBlock::GetList(
            ['ID' => 'ASC'],
            [
                "CHECK_PERMISSIONS" => "N",
                "ACTIVE" => "Y",
                "CODE" => HDESHOP_BRANDS_IBLOCK_CODE.'_'.$this->monoSite->getCode()
            ]
        )->Fetch()['ID'];

        $elements = $this->getSectionElements($iblockId);

        if (!empty($elements)) {
            foreach ($elements as $element) {
                $element['IBLOCK_ID'] = $newIiblockId;
                $iblockElements->Add($element);
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function createMonoSite()
    {
        $this->copyTemplate();
        $this->createSite();
        $this->copySiteFolders();
        $this->copySiteFiles();
        $this->copyIblocks();
        $this->copyCatalog();
        if ($this->monoSite->getType() == self::CATEGORY_TYPE) {
            $this->copyBrands();
        }
    }
}