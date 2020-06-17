<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$key = sha1('faq_' . date('Y-m-d', time()));

$cache = new \Bethowen\Helpers\FatserClaster('faqPage');

$elementsTabs = $cache->getData($key)['html'];
$paginationTabs = $cache->getData($key)['pag'];

if(isset($_REQUEST['get'])) {

    ob_start();
    ?>

    <? foreach($elementsTabs[$_REQUEST['tab']] as $key => $props): ?>
        <? if($props[2] == $_REQUEST['page']): ?>
        <div class="accordion-type-1">
            <div class="item wti" id="<?=$sub_keys;?>">
                <div class="accordion-head accordion-close" data-toggle="collapse" data-parent="#accordion<?=$key;?>">
                    <span>
                        <?=$props[0];?>
                        <i class="fa fa-angle-down"></i>
                    </span>
                </div>
                <div class="panel-collapse collapse">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="text">
                                    <div class="previewtext">
                                        <?=$props[1];?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <? endif; ?>
    <? endforeach; ?>
    <?
    $data = ob_get_clean();

    ob_start();
    ?>

    <div class="nums" data-tabs="<?=$_REQUEST['tab'];?>">
        <? if( $paginationTabs[$_REQUEST['tab']] > 1 && $_REQUEST['page'] != $paginationTabs[$_REQUEST['tab']] ): ?>
        <ul class="flex-direction-nav">
            <li class="flex-nav-next">
                <a href="javascript:;" data-page="<?=($_REQUEST['page'] + 1);?>" class="flex-next"></a>
            </li>
        </ul>
        <? endif; ?>
        <? if( $_REQUEST['page'] > 1 ): ?>
        <ul class="flex-direction-nav">
            <li class="flex-nav-prev">
                <a href="javascript:;" data-page="<?=($_REQUEST['page'] - 1);?>" class="flex-prev"></a>
            </li>
        </ul>
        <? endif; ?>
        <? for($i = 1; $i <= $paginationTabs[$_REQUEST['tab']]; $i++): ?>
            <?=($i == $_REQUEST['page']) ? "<span class=\"cur\">{$i}</span>" : "<a href=\"javascript:;\" data-page=\"{$i}\" class=\"dark_link\">{$i}</a>"; ?>
        <? endfor; ?>
    </div>

    <?
    $pag = ob_get_clean();

    echo json_encode(['html' => $data, 'pag' => $pag]);
}

if(isset($_REQUEST['pag'])) {

    ob_start();
    ?>

    <div class="nums" data-tabs="<?=$_REQUEST['tab'];?>">
        <? if( $paginationTabs[$_REQUEST['tab']] > 1 && $_REQUEST['page'] != $paginationTabs[$_REQUEST['tab']] ): ?>
        <ul class="flex-direction-nav">
            <li class="flex-nav-next">
                <a href="javascript:;" data-page="<?=($_REQUEST['page'] + 1);?>" class="flex-next"></a>
            </li>
        </ul>
        <? endif; ?>
        <? if( $_REQUEST['page'] > 1 ): ?>
        <ul class="flex-direction-nav">
            <li class="flex-nav-prev">
                <a href="javascript:;" data-page="<?=($_REQUEST['page'] - 1);?>" class="flex-prev"></a>
            </li>
        </ul>
        <? endif; ?>
        <? for($i = 1; $i <= $paginationTabs[$_REQUEST['tab']]; $i++): ?>
            <?=($i == $_REQUEST['page']) ? "<span class=\"cur\">{$i}</span>" : "<a href=\"javascript:;\" data-page=\"{$i}\" class=\"dark_link\">{$i}</a>"; ?>
        <? endfor; ?>
    </div>

    <?
    $pag = ob_get_clean();

    echo json_encode(['pag' => $pag]);

}