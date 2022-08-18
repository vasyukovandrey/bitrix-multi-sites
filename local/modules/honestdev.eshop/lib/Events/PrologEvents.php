<?php
namespace Honestdev\Eshop\Events;


use Bitrix\Main\EventManager;
use CSite;

/**
 * Class PrologEvents
 * @package Honestdev\Eshop\Events
 */
class PrologEvents extends Events
{
    public static function setUpHandlers()
    {
        $eventManager = EventManager::getInstance();
        
        $eventManager->addEventHandler("main", "OnProlog", [
            __CLASS__,
            "OnPageStartHandler"
        ]);
    }

    public static function OnPageStartHandler()
    {
        global $APPLICATION;

        if (isset($_GET['prefilter'])) {
            $_SESSION['catalog_prefilter'] = $_GET['prefilter'];
            \CUserOptions::SetOption("honestdev.eshop", 'catalog_prefilter' , $_GET['prefilter']);
        } elseif (
            !CSite::InDir('/bitrix/') &&
            !CSite::InDir('/catalog/') &&
            !CSite::InDir('/search/') ||
            $APPLICATION->GetCurPage() === '/catalog/'
        ) {
            $_SESSION['catalog_prefilter'] = "";
            \CUserOptions::SetOption("honestdev.eshop", 'catalog_prefilter' , "");
        }
    }
}