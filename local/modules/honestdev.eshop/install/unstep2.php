<?if(!check_bitrix_sessid()) return;
use Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);?>

<?=CAdminMessage::ShowNote(Loc::getMessage("MOD_UNINST_OK"));?>
<form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="<?=Loc::getMessage("MOD_BACK")?>">
</form>
