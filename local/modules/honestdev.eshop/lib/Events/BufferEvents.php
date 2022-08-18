<?php
namespace Honestdev\Eshop\Events;


use Bitrix\Main\EventManager;

/**
 * Class EpilogEvents
 * @package Honestdev\Eshop\Events
 */
class BufferEvents extends Events
{
    public static function setUpHandlers()
    {
        $eventManager = EventManager::getInstance();
        
        $eventManager->addEventHandler("main", "OnEndBufferContent", [
            __CLASS__,
            "OnEndBufferContentHandler"
        ]);
    }

    public static function OnEndBufferContentHandler(&$content)
    {
        return str_replace(' type="text/javascript"', "", $content);
    }
}