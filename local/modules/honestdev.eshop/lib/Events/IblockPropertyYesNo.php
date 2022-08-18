<?php


namespace Honestdev\Eshop\Events;


/**
 * Добавляем новый тип свойства инфоблоков - флажок "Да/Нет"
 */
class IblockPropertyYesNo
{
    function GetUserTypeDescription()
    {
        return array(
            "PROPERTY_TYPE" => "S",
            "USER_TYPE" => "YesNo",
            "DESCRIPTION" => 'Да/Нет',

            "ConvertToDB" => array(IblockPropertyYesNo::class, "ConvertToDB"),
            "ConvertFromDB" => array(IblockPropertyYesNo::class, "ConvertFromDB"),

            "GetPropertyFieldHtml" => array(IblockPropertyYesNo::class, "GetPropertyFieldHtml"),
            "GetAdminListViewHTML" => array(IblockPropertyYesNo::class, "GetAdminListViewHTML"),
            "GetPublicViewHTML" => array(IblockPropertyYesNo::class, "GetPublicViewHTML"),
        );
    }

    function ConvertToDB($arProperty, $value)
    {
        if ($value['VALUE'] === true || $value['VALUE'] === 1 || $value['VALUE'] === '1' || $value['VALUE'] === 'true' || $value['VALUE'] === 'Y') {
            $value['VALUE'] = 'Y';
        } else {
            $value['VALUE'] = 'N';
        }

        return $value;
    }

    function ConvertFromDB($arProperty, $value)
    {
        $value["VALUE"] = $value["VALUE"] == "Y" ? "Y" : "N";

        return $value;
    }

    public function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName)
    {
        return '<input name="' . htmlspecialchars($strHTMLControlName["VALUE"]) . '" id="' . htmlspecialchars($strHTMLControlName["VALUE"]) . '" value="Y" type="checkbox"' . ($arValue["VALUE"] == "Y" ? ' checked="checked"' : '') . '>';
    }

    public function GetAdminListViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        return $arValue["VALUE"] == "Y" ? 'Да' : 'Нет';
    }

    public function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        return $arValue["VALUE"] == "Y" ? 'Да' : 'Нет';
    }
}