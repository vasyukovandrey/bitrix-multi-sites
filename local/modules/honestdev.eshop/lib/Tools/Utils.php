<?php

namespace Honestdev\Eshop\Tools;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache;
use CModule;
use Honestdev\Eshop\Service\MonoSitesService;

/**
 * Инструменты
 * @package Honestdev\Eshop\Tools
 */
class Utils
{
    /**
     * Список инфоблоков
     * @var
     */
    private static $iblockList;

    /**
     * Полчить ID инфоблока по коду
     * @param string $code Код
     * @param false $clearCache Очистить кэш
     * @return mixed
     * @throws \Exception
     */
    public static function getIblockIdByCode(string $code, bool $clearCache = false, bool $checkMonoSite = true): string
    {
        if ($checkMonoSite && MonoSitesService::checkIsMonoSite()) {
            if (!in_array($code,MonoSitesService::$globalIblocks)) {
                $code = $code.'_'.MonoSitesService::getCurrentMonoSiteCode();
            }
        }

        self::$iblockList = self::getIblockList($clearCache);

        if (empty($code)) {
            throw new \InvalidArgumentException('Не передан обязательный параметр - код инфоблока');
        }

        if (!array_key_exists($code, self::$iblockList)) {
            throw new \InvalidArgumentException('Инфоблок с кодом ' . $code . ' не найден');
        }

        return self::$iblockList[$code];
    }

    /**
     * Название таблицы с одиночными свойствами инфоблока
     * @param string $iblockCode Код инфоблока
     * @return string
     * @throws \Exception
     */
    public static function getIblockSinglePropertiesTableName(string $iblockCode): string
    {
        $iblockId = Utils::getIblockIdByCode($iblockCode);
        return "b_iblock_element_prop_s$iblockId";
    }

    /**
     * Название поля свойства в таблице свойств инфоблока
     * @param string $iblockCode Код инфоблока
     * @param string $propertyCode Код свойства
     * @return string
     * @throws \Exception
     */
    public static function getIblockPropertyFieldName(string $iblockCode, string $propertyCode): string
    {
        $iblockId = Utils::getIblockIdByCode($iblockCode);

        $rsProperties = \CIBlockProperty::GetList([], [
            'IBLOCK_ID' => $iblockId,
            'CODE' => $propertyCode,
        ]);

        $propertyData = $rsProperties->Fetch();
        if (empty($propertyData) || empty($propertyData['ID'])) {
            throw new \RuntimeException("Не найденно свойство $propertyCode у инфоблока $iblockCode");
        }

        return "PROPERTY_" . $propertyData['ID'];
    }

    /**
     * Получить ID раздела по коду
     * @param mixed $code Код раздела
     * @param mixed $iblockCode Код инфоблока
     * @param false $clearCache Очистить кэш
     * @return mixed|null
     * @throws \Exception
     */
    public static function getSectionIdByCode($code, $iblockCode, $clearCache = false)
    {
        if (empty($code)) {
            throw new \InvalidArgumentException('Не передан обязательный параметр - код раздела');
        }

        $iblockId = self::getIblockIdByCode($iblockCode);

        return self::cache(
            __METHOD__ . $code . $iblockCode,
            function () use ($code, $iblockId) {
                $section = \CIBlockSection::GetList(
                    [],
                    ['IBLOCK_ID' => $iblockId, 'CODE' => $code],
                    false,
                    ['ID']
                )->Fetch();

                if (empty($section)) {
                    throw new \InvalidArgumentException('Раздел с кодом ' . $code . 'не найден');
                }

                return $section['ID'];
            },
            [],
            $clearCache
        );
    }

    /**
     * Получить список инфоблоков
     * @param false $clearCache Очистить кэш
     * @return mixed|null
     * @throws \Exception
     */
    public static function getIblockList($clearCache = false)
    {
        if (!empty(self::$iblockList)) {
            return self::$iblockList;
        }

        self::$iblockList = self::cache(
            __METHOD__,
            function () {
                CModule::IncludeModule("iblock");
                $iblockRes = \CIBlock::GetList(
                    ['ID' => 'ASC'],
                    [
                        "CHECK_PERMISSIONS" => "N",
                        "ACTIVE" => "Y",
                    ]
                );

                $iblockList = [];
                while ($iblock = $iblockRes->Fetch()) {
                    if (array_key_exists($iblock['CODE'], $iblockList)) {
                        throw new \RuntimeException('Несколько инфоблоков с одинаковым символьным кодом');
                    }
                    if ($iblock['CODE']) {
                        $iblockList[$iblock['CODE']] = $iblock['ID'];
                    }
                }
                if (empty($iblockList)) {
                    throw new \RuntimeException('Не найдено ни одного активного инфоблока');
                }
                return $iblockList;
            },
            [],
            $clearCache
        );

        return self::$iblockList;
    }

    /**
     * Кэш исполнения функции
     * @param string $uniqueString Уникальная строка
     * @param callable $function Исполняемая функция
     * @param array $tags Теги
     * @param bool $clearCache Очистить кэш
     * @param int $period Период кэширования
     * @return mixed|null
     * @throws \Exception
     */
    public static function cache(
        string   $uniqueString,
        callable $function,
        array    $tags = [],
        bool     $clearCache = false,
        int      $period = 30758400
    )
    {
        global $CACHE_MANAGER;

        $hash = md5($uniqueString);
        $cache_dir = '/app/cache/' . substr($hash, 2, 2) . '/' . $hash . '/';

        $cache = Cache::createInstance();
        if ($clearCache) {
            $cache->clean($hash);
        }
        $cache->forceRewriting($clearCache);

        if ($cache->initCache($period, $uniqueString, $cache_dir)) {
            $result = $cache->getVars()['result'];
        } elseif ($cache->startDataCache()) {
            try {
                if (!empty($tags)) {
                    $CACHE_MANAGER->StartTagCache($cache_dir);
                }
                $result = $function();
                $cache->endDataCache(array("result" => $result));
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        $CACHE_MANAGER->RegisterTag($tag);
                    }
                    $CACHE_MANAGER->EndTagCache();
                }
            } catch (\Exception $e) {
                $CACHE_MANAGER->AbortTagCache();
                $cache->abortDataCache();
                throw $e;
            }
        } else {
            //При большой нагрузке startDataCache может возвращать null из-за того, что кэш уже успел сформироваться
            // за время проверки другим пользователем
            if (!$clearCache) {
                $result = static::cache($uniqueString, $function, $tags, true, $period);
            } else {
                $result = null;
            }
        }

        return $result;
    }


    /**
     * Генерация guid
     * @param bool $dash С разделителями
     * @return string
     */
    public static function createGUID($dash = false)
    {
        if (function_exists('openssl_random_pseudo_bytes') === true) {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            if ($dash) {
                $set_uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
            } else {
                $set_uuid = vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
            }
        } else {
            $set_charid = strtoupper(md5(uniqid(random_int(PHP_INT_MIN, PHP_INT_MAX), true)));
            $set_hyphen = chr(45);
            $set_uuid = chr(123)
                . substr($set_charid, 0, 8) . $set_hyphen
                . substr($set_charid, 8, 4) . $set_hyphen
                . substr($set_charid, 12, 4) . $set_hyphen
                . substr($set_charid, 16, 4) . $set_hyphen
                . substr($set_charid, 20, 12)
                . chr(125);
        }

        return $set_uuid;
    }

    /**
     * Получение строки навигации
     * @param mixed $navObject Объект
     * @param string $template Шаблон
     * @return false|string
     */
    public static function getNavString($navObject, $template = '')
    {
        global $APPLICATION;

        $params = [
            "NAV_OBJECT" => $navObject,
            "PAGE_WINDOW" => 8,
            "SHOW_ALWAYS" => false,
        ];

        ob_start();

        $APPLICATION->IncludeComponent(
            "bitrix:main.pagenavigation",
            $template,
            $params,
            false,
            [
                "HIDE_ICONS" => "Y",
            ]
        );

        return ob_get_clean();
    }

    /**
     * Получение массива-дерева из линейного массива
     * @param array $elements Элементы
     * @param string $fieldId ID поля
     * @param string $fieldParentId ID поля родителя
     * @param string $fieldChildren Поле дочерних
     * @param null $parentId ID родителя
     * @return array
     */
    public static function getTreeFromArray(
        array &$elements,
              $fieldId = 'id',
              $fieldParentId = 'parentId',
              $fieldChildren = 'children',
              $parentId = null
    ): array
    {
        $branch = [];
        foreach ($elements as &$element) {
            if ($element[$fieldParentId] == $parentId) {
                $children = self::getTreeFromArray(
                    $elements,
                    $fieldId,
                    $fieldParentId,
                    $fieldChildren,
                    $element[$fieldId]
                );
                if ($children) {
                    $element[$fieldChildren] = $children;
                }
                $branch[$element[$fieldId]] = $element;
                unset($element);
            }
        }
        return $branch;
    }

    /**
     * Замена первого вхождения в строку
     * @param string $from Вхождение
     * @param string $to Замена
     * @param string $content Контент
     * @return string
     */
    public static function strReplaceFirst(string $from, string $to, string $content): string
    {
        $from = '/' . preg_quote($from, '/') . '/';
        return preg_replace($from, $to, $content, 1);
    }

    /**
     * Сгруппировать массив по столбцу
     * @param array $array Массив
     * @param string $column Колонка
     * @param bool $saveKey Сохранить ключ
     * @return array
     */
    public static function groupArrayByColumn(array $array, string $column, bool $saveKey = false): array
    {
        $groupArray = [];

        foreach ($array as $key => $value) {
            if (isset($value[$column])) {
                if ($saveKey) {
                    $groupArray[$value[$column]][$key] = $value;
                } else {
                    $groupArray[$value[$column]][] = $value;
                }
            }
        }

        return $groupArray;
    }

    /**
     * Транслит
     * @param string $value Значение
     * @param array $params Параметры
     * @param string $separator Разделитель
     * @return string
     */
    public static function translit(string $value, array $params = [], $separator = '_')
    {
        $converter = [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ь' => '',
            'ы' => 'y',
            'ъ' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',

            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'E',
            'Ж' => 'Zh',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'Y',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Sch',
            'Ь' => '',
            'Ы' => 'Y',
            'Ъ' => '',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya',
            ' ' => $separator
        ];

        $value = strtr($value, $converter);

        if ($params['change_case'] == 'U') {
            $value = strtoupper($value);
        }

        if ($params['change_case'] == 'L') {
            $value = strtolower($value);
        }

        return $value;
    }

    /**
     * Получение id разделов с цепочкой родительских
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getSectionTreeIdsByIds(array $sectionIds): array
    {
        $sections = \Bitrix\Iblock\SectionTable::getList(
            [
                'filter' => [
                    'ID' => array_keys($sectionIds)
                ],
                'select' => [
                    'ID',
                    'IBLOCK_SECTION_ID'
                ]
            ]
        )->fetchAll();

        $findParents = false;
        $parentIds = [];

        foreach ($sections as $section) {
            if (!empty($section['IBLOCK_SECTION_ID'])) {
                $findParents = true;
                $parentIds[$section['IBLOCK_SECTION_ID']] = $section['IBLOCK_SECTION_ID'];
            }
        }

        if ($findParents) {
            return array_merge_recursive($sectionIds,self::getSectionTreeIdsByIds($parentIds));
        } else {
            return $sectionIds;
        }
    }

    public static function copyFolder($from, $to, $rewrite = true) {
        if (is_dir($from)) {
            @mkdir($to);
            $d = dir($from);
            while (false !== ($entry = $d->read())) {
                if ($entry == "." || $entry == "..") {
                    continue;
                };
                self::copyFolder("$from/$entry", "$to/$entry", $rewrite);
            }
            $d->close();
        } else {
            if (!file_exists($to) || $rewrite)
                copy($from, $to);
        }
    }
}