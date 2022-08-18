<?

use Bitrix\Main\Context;
use \Bitrix\Main\Localization\Loc;

/**
 * Class honestdev_eshop
 */
class honestdev_eshop extends CModule
{
    var $MODULE_ID;
    var $MODULE_NAME;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    /**
     * app_module constructor.
     */
    function __construct()
    {
        $arModuleVersion = [];

        include(__DIR__ . "/version.php");
        require_once(__DIR__ . "/../include/defines.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_ID = HDESHOP_MODULE_ID;
        $this->MODULE_NAME = "Расширение интернет магазина для Technosad";
        $this->MODULE_DESCRIPTION = "Расширение интернет магазина для Technosad";
        $this->PARTNER_NAME = "HONESTDEV";
        $this->PARTNER_URI = "https://honestdev.ru";
    }

    /**
     * Install Files Module
     *
     * @return bool|void
     */
    function InstallFiles()
    {
        return true;
    }

    /**
     * Uninstall Files Module
     *
     * @return bool|void
     */
    function UnInstallFiles()
    {
        return true;
    }

    /**
     * Матрица ролей и операций доступных в модуле
     * @return array
     */
    function GetModuleTasks()
    {
        return [];
    }

    /**
     * Возвращает список ORM таблиц модуля для установки/удаления
     * @return string[]
     */
    function getModuleTables()
    {
        return [];
    }

    /**
     * Возвращает дефолтные массивы данных для установки таблиц
     * @return string[]
     */
    function getDefaultTablesData()
    {
        return [];
    }

    /**
     * Install module DB tables
     * @return bool
     */
    function InstallDB()
    {
        global $DB;

        $tables = $this->getModuleTables();
        $defaultTablesData = $this->getDefaultTablesData();
        foreach ($tables as $class) {
            if (!$DB->Query("SHOW TABLES LIKE '" . $class::getTableName() . "';")->Fetch()) {
                $class::getEntity()->createDbTable();

                if (!empty($defaultTablesData[ $class ])) {
                    foreach ($defaultTablesData[ $class ] as $type) {
                        try {
                            $class::add($type);
                        } catch (\Exception $exception ) {}
                    }
                }
            }
        }
        return true;
    }

    /**
     * Uninstall module DB tables
     * @param array $arParams
     * @return bool
     */
    function UnInstallDB($arParams = [])
    {
        global $DB;

        if (empty($arParams["savedata"]) || $arParams["savedata"] != "Y") {
            $tables = $this->getModuleTables();
            foreach ($tables as $class) {
                $sql = "DROP TABLE IF EXISTS " . $class::getTableName() . " ;";
                $DB->Query($sql)->Fetch();
            }
        }

        return true;
    }

    function installEvents() {

    }

    function unInstallEvents() {

    }

    /**
     *  Install Module
     *
     * @return bool|void
     */
    function DoInstall()
    {
        $this->InstallFiles();
        RegisterModule($this->MODULE_ID);
        $this->installEvents();
        $this->InstallTasks();
        $this->InstallDB();
        return true;
    }

    /**
     *  Uninstall Module
     *
     * @return bool|void
     */
    function DoUninstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT, $step;
        $step = (int)$step;

        $context = Context::getCurrent()->getRequest();

        $this->UnInstallDB([
            "savedata" => $context->get('savedata'),
        ]);

        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
        $this->unInstallEvents();
        $this->UnInstallTasks();
        $APPLICATION->IncludeAdminFile(Loc::getMessage("MTS_KB_UNINSTALL_MESS"), $DOCUMENT_ROOT . "/local/modules/" . $this->MODULE_ID . "/install/unstep2.php");

        return true;
    }
}
