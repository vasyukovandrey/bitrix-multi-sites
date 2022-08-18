<?php
namespace Honestdev\Eshop\Events;


use Bitrix\Main\Application;
use Bitrix\Main\DB\Exception;
use Bitrix\Main\EventManager;
use Bitrix\Main\Web\Cookie;

/**
 * Class UserEvents
 * @package Honestdev\Eshop\Events
 */
class UserEvents extends Events
{
    public static function setUpHandlers()
    {
        $eventManager = EventManager::getInstance();
        
        $eventManager->addEventHandler("iblock", "OnIBlockPropertyBuildList", [
            IblockPropertyYesNo::class,
            "GetUserTypeDescription"
        ]);

        $eventManager->addEventHandler("main", "OnBeforeUserRegister", [
            __CLASS__,
            "OnBeforeUserRegisterHandler"
        ]);

        $eventManager->addEventHandler("main", "OnAfterUserAuthorize", [
            __CLASS__,
            "OnAfterUserAuthorizeHandler"
        ]);
    }

    public static function OnBeforeUserRegisterHandler(&$arFields) {
        $arFields["LOGIN"] = $arFields["EMAIL"];
        $arFields["CONFIRM_PASSWORD"] = $arFields["PASSWORD"];
    }

    public static function OnAfterUserAuthorizeHandler(&$arFields)
    {
        try {
            if ($arFields["user_fields"]['ID'] > 0) {
                $application = Application::getInstance();
                $context = $application->getContext();
                $request = $context->getRequest();

                $currentFav = $request->getCookie("favorites");
                $currentFav = unserialize($currentFav);
                global $USER;
                $idUser = $arFields["user_fields"]['ID'];
                $rsUser = \CUser::GetByID($idUser);
                $arUser = $rsUser->Fetch();
                $arElements = unserialize($arUser['UF_FAVORITES']);
                if ($currentFav) {
                    foreach ($currentFav as $id) {
                        if (!in_array($id, $arElements))
                            $arElements[] = $id;
                    }
                    $USER->Update($idUser, Array("UF_FAVORITES" => serialize($arElements)));
                }
                $cookie = new Cookie("favorites", '', time() - 60);
                $cookie->setDomain($context->getServer()->getHttpHost());
                $cookie->setHttpOnly(false);

                $context->getResponse()->addCookie($cookie);
                $context->getResponse()->flush("");
            }
        } catch (Exception $e) {
            AddMessage2Log($e->getMessage());
        }
    }
}