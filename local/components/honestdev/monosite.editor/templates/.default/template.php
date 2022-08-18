<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<table border="1" style="width: 100%;margin-bottom: 40px">
    <tbody>
    <tr>
        <th>ID</th>
        <th>ID сайта</th>
        <th>Код</th>
        <th>Имя</th>
        <th>Домен</th>
        <th>Тип</th>
        <th>Привязка</th>
    </tr>
    <?
    foreach ($arResult['ITEMS'] as $item) {
        ?>
        <tr>
            <td><?=$item['ID']?></td>
            <td><?=$item['UF_SITE_ID']?></td>
            <td><?=$item['UF_CODE']?></td>
            <td><?=$item['UF_NAME']?></td>
            <td><?=$item['UF_DOMAIN']?></td>
            <td><?=$arResult['TYPES'][$item['UF_TYPE']]?></td>
            <td>
            <?
            switch ($item['UF_TYPE']) {
                case 1:
                    echo $arResult['CATEGORY'][$item['UF_ENTITY_ID']];
                    break;
                case 2:
                    echo $arResult['BRANDS'][$item['UF_ENTITY_ID']];
                    break;
            }
            ?>
            </td>
        </tr>
        <?
    }
    ?>
    </tbody>
</table>
<div class="create-form">
    <div class="Form__section">
        <label class="TextInput">
            <div class="TextInput__title">Код сайта</div>
            <input class="TextInput__input js-siteCode" type="text" name="code" value="" placeholder="abcd">
        </label>
    </div>
    <div class="Form__section">
        <label class="TextInput">
            <div class="TextInput__title">Имя</div>
            <input class="TextInput__input js-siteName" type="text" name="name" value="" placeholder="Site name">
        </label>
    </div>
    <div class="Form__section">
        <label class="TextInput">
            <div class="TextInput__title">Домен</div>
            <input class="TextInput__input js-siteDomain" type="text" name="domain" value="" placeholder="domain.ru">
        </label>
    </div>
    <div class="Form__section">
        <select name="type" class="js-type">
            <option>Выбрат тип</option>
            <?
            foreach ($arResult['TYPES'] as $id => $type) {
                ?>
                <option value="<?=$id?>"><?=$type?><br>
                <?
            }
            ?>
        </select>
    </div>
    <div class="Form__section js-category" style="display:none;">
        <select name="category">
            <option>Выбрат категорию</option>
            <?
            foreach ($arResult['CATEGORY'] as $id => $category) {
                ?>
                <option value="<?=$id?>"><?=$category?></option>
                <?
            }
            ?>
        </select>
    </div>
    <div class="Form__section js-brands" style="display:none;">
        <select name="brands">
            <option>Выбрат бренд</option>
            <?
            foreach ($arResult['BRANDS'] as $id => $brand) {
                ?>
                <option value="<?=$id?>"><?=$brand?></option>
                <?
            }
            ?>
        </select>
    </div>
<button class="Button Button_size_m js-addSite">Добавить моносайт</button>
</div>
<script>
    $(document).ready(function() {
        $('.js-type').on('change', function(){
            const val = $(this).val();

            if  (val == 1) {
                $('.js-category').show();
                $('.js-brands').hide();
            } else if(val == 2) {
                $('.js-category').hide();
                $('.js-brands').show();
            } else {
                $('.js-category').hide();
                $('.js-brands').hide();
            }
        });

        $('.js-addSite').on('click', function(){
            BX.showWait();
            const siteCode = $('.js-siteCode').val();
            const siteName = $('.js-siteName').val();
            const siteDomain = $('.js-siteDomain').val();
            const siteType = $('.js-type').val();
            let entityId = '';

            if (siteType !== '1') {
                entityId = $('.js-brands select').val();
            } else {
                entityId = $('.js-category select').val();
            }

            const request = BX.ajax.runComponentAction(
                'honestdev:monosite.editor',
                'createMonoSite', {
                    mode: 'class',
                    data: {
                        SITE_ID: 's1',
                        sessid: BX.message('bitrix_sessid'),
                        code: siteCode,
                        name: siteName,
                        domain: siteDomain,
                        type: siteType,
                        entityId: entityId
                    }
                }
            );

            request.then(function (response) {
                BX.closeWait();
                window.location.reload();
            }, function (response) {
                BX.closeWait();
                const Confirmer = new BX.PopupWindow("", null, {
                    content: '<p style="padding: 20px">Ошибка в выполнении скрипта<br>' + response.errors[1].message + '</p>',
                    zIndex: 0,
                    autoHide: true,
                    offsetTop: 1,
                    offsetLeft: 0,
                    lightShadow: true,
                    closeIcon: true,
                    closeByEsc: true,
                    draggable: {restrict: false},
                    overlay: {backgroundColor: 'black', opacity: '80'},
                    buttons: [
                        new BX.PopupWindowButton({
                            text: "Позвать Андрюху",
                            className: "popup-window-button-accept",
                            events: {
                                click: function () {
                                    this.popupWindow.close();
                                    window.open('https://t.me/undefined100503', '_blank');
                                }
                            }
                        }),
                    ]
                });

                Confirmer.show();
            });
        });
    });
</script>