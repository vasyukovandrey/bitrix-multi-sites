<?php

namespace Honestdev\Eshop;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Honestdev\Eshop\Base\Singleton;
use Honestdev\Eshop\Events\Events;

/**
 * Веб-приложение Интернет магазин садовой техники
 * @package Honestdev\Eshop
 */
class HonestDevEshop
{
    use Singleton;

    /**
     * ID модуля
     * @var string
     */
    protected $moduleId = HDESHOP_MODULE_ID;
    /**
     *  Инициализация приложения
     */
    public function init()
    {
        //Устанавливаем обработчики событий
        Events::setUpHandlers();
    }

    /**
     * Получение Id модуля СУЗ
     * @return string
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * Задание опции модуля
     * @param mixed $name Название
     * @param string $value Значение
     * @param string $siteId ID сайта
     * @throws ArgumentOutOfRangeException
     */
    public function setOption(mixed $name, string $value = "", string $siteId = "")
    {
        Option::set($this->moduleId, $name, $value, $siteId);
    }

    /**
     * Получение зависимостей модуля
     * @return array
     */
    public static function OnGetDependentModule(): array
    {
        return [
            'MODULE_ID' => HDESHOP_MODULE_ID,
            'USE' => array("PUBLIC_SECTION")
        ];
    }
}