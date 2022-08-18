<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;

require_once(__DIR__ . "/include/defines.php");

if (!$USER->IsAdmin()) {
    return;
}

$request = Context::getCurrent()->getRequest();
$moduleId = HDESHOP_MODULE_ID;

$aTabs = [
    [
        'DIV' => 'main_options',
        'TAB' => Loc::getMessage('HDESHOP_MODULE_TAB_MAIN'),
        'OPTIONS' => [
            Loc::getMessage('HDESHOP_MODULE_SECTION_SECTION'),
            [
                'TEST',
                Loc::getMessage('HDESHOP_MODULE_PARAM_TEST'),
                null,
                ['text', 52],
            ]
        ]
    ]
];

if ($request->isPost() && strlen($request->getPost('save')) > 0 && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab) {
        __AdmSettingsSaveOptions($moduleId, $aTab['OPTIONS']);
    }

    LocalRedirect($APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . urlencode($moduleId) .
        '&tabControl_active_tab=' . urlencode($_REQUEST['tabControl_active_tab']) . '&sid=' . urlencode($siteId));
}

$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>
    <form method='post' action='' name='bootstrap'>
        <?
        $tabControl->Begin();

        foreach ($aTabs as $aTab) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($moduleId, $aTab['OPTIONS']);
        }

        $tabControl->Buttons([
            'btnApply' => false,
            'btnCancel' => false,
            'btnSaveAndAdd' => false,
        ]);
        ?>

        <?= bitrix_sessid_post(); ?>
        <? $tabControl->End(); ?>
    </form>
<?
