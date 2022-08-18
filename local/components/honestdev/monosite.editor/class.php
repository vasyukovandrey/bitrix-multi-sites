<?php

use Bitrix\Main\Application;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\ActionFilter;
use Honestdev\Eshop\Repository\MonoSitesRepository;
use Honestdev\Eshop\Service\MonoSitesService;
use Honestdev\Eshop\Tools\Utils;

use Bitrix\Main;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}


class MonoSiteEditor extends \CBitrixComponent implements Controllerable
{
    private $monoSiteRep;

    /**
     * @throws Main\LoaderException
     */
    public function __construct($component = null)
    {
        Loader::includeModule("iblock");
        Loader::includeModule(HDESHOP_MODULE_ID);
        global $USER;
        $this->user = $USER;
        $this->monoSiteRep = new MonoSitesRepository();
        parent::__construct($component);
    }

    /**
     * @throws Main\Db\SqlQueryException
     * @throws Main\SystemException
     * @throws Exception
     */
    public function createMonoSiteAction()
    {
        $request = $this->request->getPostList()->getValues();
        if (
            empty($request['code']) ||
            empty($request['name']) ||
            empty($request['domain']) ||
            empty($request['type']) ||
            empty($request['entityId'])
        )
        {
            throw new \InvalidArgumentException('Не передано обязательное поле');
        }

        if (!empty($this->monoSiteRep->getElement(['filter' => ['UF_CODE' => $request['code']]]))) {
            throw new \InvalidArgumentException('Не уникальный код сайта');
        }

        $obSite = new CSite;
        $siteId = 0;
        $rsSites = $obSite::GetList($by,$order,[]);

        while ($site = $rsSites->Fetch()) {
            $curId = (int) trim($site['LID'], 's');
            if ($curId > $siteId) {
                $siteId = $curId + 1;
            }
        }

        $element = $this->monoSiteRep->getModel();
        $element
            ->setSiteId('s'.$siteId)
            ->setCode($request['code'])
            ->setName($request['name'])
            ->setDomain($request['domain'])
            ->setType($request['type'])
            ->setEntityId($request['entityId']);

        //$connection = Application::getInstance()->getConnection();
        //$connection->startTransaction();


            $newElement = $this->monoSiteRep->saveElement($element);
            (new MonoSitesService($newElement))->createMonoSite();


        return [];
    }

    private function getBrands(): array
    {
        $result = [];

        $rs = CIBlockElement::GetList(
            [
                'NAME' => 'asc'
            ],
            [
                'IBLOCK_ID' => Utils::getIblockIdByCode(HDESHOP_BRANDS_IBLOCK_CODE)
            ],
            false,
            false,
            [
                'ID',
                'IBLOCK_ID',
                'NAME',
            ]
        );

        while ($arItem = $rs->GetNext()) {
            $result[$arItem['ID']] = $arItem['NAME'];
        }

        return $result;
    }

    private function getCategory(): array
    {
        $result = [];

        $rs = \CIBlockSection::GetList(
            [
                'NAME' => 'asc'
            ],
            [
                'IBLOCK_ID' => Utils::getIblockIdByCode(HDESHOP_CATALOG_IBLOCK_CODE)
            ],
            false,
            ['ID','NAME']
        );

        while ($arSection = $rs->GetNext()) {
            $result[$arSection['ID']] = $arSection['NAME'];
        }

        return $result;
    }

    public function executeComponent()
    {
        CJSCore::Init(["jquery"]);

        $this->arResult['ITEMS'] = $this->monoSiteRep->getList();
        $this->arResult['TYPES'] = [
            MonoSitesService::CATEGORY_TYPE => 'Моно-категория',
            MonoSitesService::BRAND_TYPE => 'Моно-бренд',
        ];
        $this->arResult['BRANDS'] = $this->getBrands();
        $this->arResult['CATEGORY'] = $this->getCategory();
        $this->includeComponentTemplate();
    }

    /**
     * @return array|array[][]
     */
    public function configureActions()
    {
        return [
            'createMonoSite' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [
                            ActionFilter\HttpMethod::METHOD_GET,
                            ActionFilter\HttpMethod::METHOD_POST,
                        ]
                    ),
                ],
                'postfilters' => [],
            ]
        ];
    }
}