<?
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/vendor/autoload.php';
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Honestdev\Eshop\HonestDevEshop;

require(__DIR__."/include/useful_functions.php");

if(ModuleManager::isModuleInstalled('honestdev.eshop') && Loader::includeSharewareModule("honestdev.eshop")) {
    HonestDevEshop::getInstance()->init();
}
