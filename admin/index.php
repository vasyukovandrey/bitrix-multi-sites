<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Управление моносайтами");
@set_time_limit(0);
ini_set('memory_limit', '-1');
global $USER;?>
<div class="container">
    <?
    if (!$USER->isAdmin()) {
        ?>доступ запрещён<?
    } else {
        $APPLICATION->IncludeComponent("honestdev:monosite.editor", "", array());
    }
    ?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");