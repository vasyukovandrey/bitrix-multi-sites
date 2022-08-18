<?php

namespace Honestdev\Eshop\Base;

/**
 * Базовое хранилище
 * @package Honestdev\Eshop\Tools
 */
abstract class BaseStorage
{
    use Singleton;

    /**
     * Объекты
     * @var array
     */
    protected array $entities = [];

    /**
     * Получение ключей объектов
     * @param mixed $entityKeys Ключи объектов
     * @return string
     */
    private static function hashEntityKeys($entityKeys): string
    {
        if (is_string($entityKeys)) {
            return $entityKeys;
        }
        if (is_array($entityKeys)) {
            sort($entityKeys);
        }
        return md5(serialize($entityKeys));
    }

    /**
     * Получение данных объекта
     * @param mixed $entityType Тип объекта
     * @param mixed $entityKeys Ключи объекта
     * @param mixed $entityData Данные объекта
     * @return bool
     */
    protected static function addEntityData($entityType, $entityKeys, $entityData): bool
    {
        $entityKeys = self::hashEntityKeys($entityKeys);

        if (!$existingData = static::getEntityData($entityType, $entityKeys)) {
            static::getInstance()->entities[$entityType][$entityKeys] = $entityData;
        } else {
            static::getInstance()->entities[$entityType][$entityKeys] = array_merge($existingData, $entityData);
        }

        return (boolean)static::getEntityData($entityType, $entityKeys);
    }

    /**
     * Получение данных объекта
     * @param mixed $entityType Тип объекта
     * @param mixed $entityKeys Ключи объекта
     * @return mixed
     */
    protected static function getEntityData($entityType, $entityKeys): mixed
    {
        return static::getInstance()->entities[$entityType][self::hashEntityKeys($entityKeys)];
    }
}
