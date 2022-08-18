<?php

namespace Honestdev\Eshop\Events;

/**
 * Class Events
 * @package Honestdev\Eshop\Events
 */
class Events
{
    /**
     *  Установка событий
     */
    public static function setUpHandlers()
    {
        UserEvents::setUpHandlers();

        EpilogEvents::setUpHandlers();

        PrologEvents::setUpHandlers();

        BufferEvents::setUpHandlers();

        CatalogEvents::setUpHandlers();
    }
}
