<?php
namespace Honestdev\Eshop\Events;


use Bitrix\Main\EventManager;

/**
 * Class EpilogEvents
 * @package Honestdev\Eshop\Events
 */
class EpilogEvents extends Events
{
    public static function setUpHandlers()
    {
        $eventManager = EventManager::getInstance();
        
        $eventManager->addEventHandler("main", "OnEpilog", [
            __CLASS__,
            "OnEpilogHandler"
        ]);

        $eventManager->addEventHandler("main", "OnAfterEpilog", [
            __CLASS__,
            "OnEpilogHandler"
        ]);
    }

    public static function OnEpilogHandler()
    {
        if (isset($_GET['PAGEN_1']) && intval($_GET['PAGEN_1'])>0) {
            $title = $GLOBALS['APPLICATION']->GetTitle();
            $GLOBALS['APPLICATION']->SetPageProperty('title', $title.' – Страница '.intval($_GET['PAGEN_1']));
            $description = $GLOBALS['APPLICATION']->GetProperty("description");
            $GLOBALS['APPLICATION']->SetPageProperty('description', $description.' – Страница '.intval($_GET['PAGEN_1']));
            $keywords = $GLOBALS['APPLICATION']->GetProperty("keywords");
            $GLOBALS['APPLICATION']->SetPageProperty('keywords', $keywords.' – Страница '.intval($_GET['PAGEN_1']));
        }
    }

    public static function OnAfterEpilog()
    {
        global $APPLICATION;
        if (!defined('ERROR_404') || ERROR_404 != 'Y') {
            return;
        }
        if ($APPLICATION->GetCurPage() != PAGE_404) {
            http_response_code(404);
            exit();
        }
    }
}