<?php

namespace Honestdev\Eshop\Base;

/**
 * Singleton
 * @package namespace Honestdev\Eshop\Tools;
 */
trait Singleton
{
    /**
     * Хранимый объект класса
     * @var static
     */
    protected static $_instance = null;

    /**
     * Конструктор класса
     * private запрещает создание новых объектов
     */
    private function __construct()
    {
    }

    /**
     * private запрещает клонирование объекта
     */
    private function __clone()
    {
    }

    /**
     * Возвращает объект класса-одиночки
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
}
