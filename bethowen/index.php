<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

Bitrix\Main\Page\Asset::getInstance()->addJs("/sale/script.js");
Bitrix\Main\Page\Asset::getInstance()->addCss("/sale/style.css");
Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/templates/aspro_next/js/device.js");
Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/templates/aspro_next/js/slick.min.js");
Bitrix\Main\Page\Asset::getInstance()->addCss("/bitrix/templates/aspro_next/css/slick.css");
$APPLICATION->SetTitle("Акции");

?>
<?

$selectAction = new Bethowen\Services\ActionSelector();

$selectAction->StaticData(ASPRO_ACTION);

$selectAction->getData(ASPRO_ACTION, '');

?>

<div class="row">
    <div class="head-img">
        <img class="img" title="top-baner" alt="top baner action" src="https://www.bethowen.ru/upload/medialibrary/1f4/1f4af25a0a7cabc1c43cb374c741746f.jpg" />
    </div>

    <div class="container-filter-top">
        <? $i = 0; ?>
        <? foreach($selectAction->dataIconsSection as $item): ?>
            <div data-filter="<?=$item['code'];?>" class="outer-container<?=($i == 0) ? ' active': '' ?>" style="flex-basis: calc(99.5% / 7);">
                <div class="container-item" style="background: url(<?=($i == 0) ? $item['ico_active']:$item['ico_noact']?>);" data-act-ico="<?=$item['ico_active'];?>" data-nact-ico="<?=$item['ico_noact'];?>">
                </div>
                <div class="container-item" style="display: none; background: url(<?=($i == 0) ? $item['ico_noact']:$item['ico_active']?>);">
                </div>
                <div class="text-name text-center">
                    <?=$item['name'];?>
                </div>
            </div>
            <? $i++; ?>
        <? endforeach; ?>
    </div>

    <div class="container-filter-action" data-info="" data-section="<?=ASPRO_ACTION;?>">
        <? foreach($selectAction->dataActions as $item): ?>
            <div class="items-block">
                <div class="sun-shine" data-item="<?=$item['id']?>"></div>
                <? if($item['link'] != ''): ?>
                <a class="no-decor" target="_blank" id="<?=$item['id'];?>" href="<?=$item['link'];?>">
                    <img title="<?=$item['name'];?>" src="<?=$item['image'];?>"  alt="<?=$item['name'];?>" />
                    <div class="text-period">
                        <img alt="cal-ico" src="/images/calendar.svg" /> <?=$item['date_period'];?>
                    </div>
                </a>
                <? else: ?>
                <img src="<?=$item['image'];?>" title="<?=$item['name'];?>" alt="<?=$item['name'];?>" />
                <div class="text-period">
                    <img alt="cal-ico" src="/images/calendar.svg" /> <?=$item['date_period'];?>
                </div>
                <? endif; ?>
            </div>
        <? endforeach; ?>
    </div>
</div>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
