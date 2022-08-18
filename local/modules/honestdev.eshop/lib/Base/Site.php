<?php

namespace Honestdev\Eshop\Base;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SiteTable;
use Bitrix\Main\SystemException;

/**
 * Сайт
 * @package Honestdev\Eshop\Tools
 */
class Site
{
    use Singleton;

    /**
     * Данные сайта
     * @var array
     */
    private array $siteData = [];

    /**
     * Внутрений геттер-сеттер параметров сайта
     * @param mixed $key Ключ
     * @param null $value Значение
     * @return mixed
     */
    private static function current($key, $value = null): mixed
    {
        if (!is_null($value)) {
            self::getInstance()->siteData[$key] = $value;
        }
        return self::getInstance()->siteData[$key];
    }

    /**
     * Возвращает LID сайта
     * Если вызывается в административной панели, возвращает LID активного сайта с наименьшим весом сортировки
     * @return mixed
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getId()
    {
        if (!self::current('id')) {
            if (
                ADMIN_SECTION == true
                && SITE_ID == LANGUAGE_ID
            ) {
                $lid = SiteTable::getList(
                    [
                        'select' => ['LID'],
                        'filter' => ['ACTIVE' => 'Y'],
                        'order' => ['SORT' => 'ASC'],
                        'cache' => ['ttl' => 3600],
                    ]
                )->fetch()['LID'];
            } else {
                $lid = SITE_ID;
            }
            self::current('id', $lid);
        }
        return self::current('id');
    }

    /**
     * Возвращает значение поля "URL сервера" в настройках текущего сайта
     * @return mixed
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getUrlFromBase()
    {
        if (!self::current('url-from-base')) {
            $url = SiteTable::getList(
                [
                    'filter' => ['LID' => self::getId()],
                    'limit' => 1,
                    'cache' => ['ttl' => 3600],
                ]
            )->fetch();
            self::current('url-from-base', $url['SERVER_NAME']);
        }
        return self::current('url-from-base');
    }
}
