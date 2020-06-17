<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Вопросы и ответы");

define('PAGINATION_ITEMS', 10);

class PerformDataGetter {

    public $arrElements;
    public $arrPagination;
    public $arrSection;

    public function __construct() {
        $this->arrElements = [];
        $this->arrPagination = [];
        $this->arrSection = [];
    }
    //38
    public function getDataSection ($id_iblock) {
        if(empty($id_iblock)) {
            return false;
        }
        $items = GetIBlockSectionList($id_iblock, 0, array("SORT"=>"ASC"), false);

        while($elem = $items->Fetch()) {
            if($elem['ACTIVE'] == 'Y') {
                $this->arrSection[$elem['ID']] = $elem['NAME'];
            }
        }

        return $this->arrSection;
    }

    public function getDataElements ($id_iblock) {
        if(empty($id_iblock)) {
            return false;
        }
        $elementsFaq = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array('IBLOCK_ID' => $id_iblock, 'ACTIVE' => 'Y'),
            false,
            false,
            array()
        );

        $page = [];

        while($extrudeData = $elementsFaq->Fetch()) {
            if(!isset($page[$extrudeData['IBLOCK_SECTION_ID']])) {
                $page[$extrudeData['IBLOCK_SECTION_ID']] = 0;
            }

            $page[$extrudeData['IBLOCK_SECTION_ID']] += 1;
            $pag = ceil($page[$extrudeData['IBLOCK_SECTION_ID']] / PAGINATION_ITEMS);
            $this->arrPagination[$extrudeData['IBLOCK_SECTION_ID']] = $pag;

            $this->arrElements[$extrudeData['IBLOCK_SECTION_ID']][$extrudeData['ID']] = [
                $extrudeData['NAME'],
                $extrudeData['PREVIEW_TEXT'],
                $pag,
            ];
        }

        return ['els' => $this->arrElements, 'pag' => $this->arrPagination];
    }
}

$dataIb = new PerformDataGetter();

$cache = new \Bethowen\Helpers\FasterClaster('faqPage');

$key = sha1('faq_' . date('Y-m-d', time()));


if($cache->getData($key) == null) {

    $dataSet = $dataIb->getDataElements(38);
    $elementsTabs = $dataSet['els'];
    $paginationTabs = $dataSet['pag'];

    $sections = $dataIb->getDataSection(38);

    $cache->setDataTimout($key, ['html' => $elementsTabs, 'pag' => $paginationTabs, 'sec' => $sections], 60*60*24);
} else {

    $elementsTabs = $cache->getData($key)['html'];
    $paginationTabs = $cache->getData($key)['pag'];
    $sections = $cache->getData($key)['sec'];

    //this we protected data in some reason cache is empty we get data directely
    if(empty($elementsTabs) || empty($paginationTabs)) {
        $dataSet = $dataIb->getDataElements(38);
        $elementsTabs = $dataSet['els'];
        $paginationTabs = $dataSet['pag'];
        $cache->setDataTimout($key, ['html' => $elementsTabs, 'pag' => $paginationTabs, 'sec' => $cache->getData($key)['sec']], 60*60*24);
    }

    if(empty($sections)) {
        $sections = $dataIb->getDataSection(38);
        $cache->setDataTimout($key, ['html' => $elementsTabs, 'pag' => $paginationTabs, 'sec' => $sections], 60*60*24);
    }
}

$cache->close();

?>

<script type="text/javascript">
    var cur_page = Array();
    $.when($.ready).then(function() {
        $('[data-toggle="tab"]').on('click', function() {
            var tab = $(this).attr('href').split('_')[2];
            var page = 1;
            $('.tab-pane').removeClass('active');
            $(String($(this).attr('href'))).addClass('active');
            //$('.nums').data('tabs', tab);
            if(cur_page[tab] != null) {
                page = cur_page[tab];
            }
            $.ajax({
                url: '/faq/ajax.php',
                type: 'POST',
                dataType: 'json',
                data: { 'page' : page, 'tab' : tab, 'pag' : 'true' },
                success: function(data) {
                    $('.module-pagination').html(data.pag);
                }
            });
        });

        $('body').on('click', '[data-toggle="collapse"]' , function() {
            //$('.item').removeClass('opened');
            for(var i = 0; i < $('.panel-collapse').length; i++) {
                if($($('.panel-collapse')[i]).hasClass('collapse') == false && last_item != $(this).parents('.item').attr('id')) {
                    $($('.panel-collapse')[i]).addClass('collapse');
                    $($('.panel-collapse')[i]).parents('.item').removeClass('opened');
                    $($('.panel-collapse')[i]).siblings('.accordion-head').removeClass('accordion-open').addClass('accordion-close');
                }
            }
            //console.log($(this).parents('.item').hasClass('opened'));
            switch($(this).parents('.item').hasClass('opened')) {
                case true:
                    //console.log('1122');
                    $(this).parents('.item').removeClass('opened');
                    $(this).siblings('.panel-collapse').addClass('collapse');
                    $(this).siblings('.panel-collapse').siblings('.accordion-head').removeClass('accordion-open').addClass('accordion-close');
                    break;
                case false:
                    //console.log('1133');
                    $(this).parents('.item').addClass('opened');
                    last_item = $(this).parents('.item').attr('id');
                    $(this).siblings('.panel-collapse').removeClass('collapse');
                    $(this).siblings('.panel-collapse').siblings('.accordion-head').removeClass('accordion-close').addClass('accordion-open');
                    break;
            }
            //$(this).find('panel-collapse').removeClass('collapse');
        });

        $('body').on('click', '.nums a', function () {
            cur_page[$(this).parents('.nums').data('tabs')] = $(this).data('page');
            $.ajax({
                url: '/faq/ajax.php',
                type: 'POST',
                dataType: 'json',
                data: { 'page' : $(this).data('page'), 'tab' : $(this).parents('.nums').data('tabs'), 'get' : 'true' },
                success: function(data) {
                    $('.tab-pane.active').html(data.html);
                    $('.module-pagination').html(data.pag);
                }
            });
        });
    });
</script>

<div>В данном разделе приведены ответа на часто задаваемые вопросы посетителей Вашего будущего сайта. Использование данного раздела позволит сократить нагрузку на операторов и повысить удовлетворенность ваших клиентов. </div>
<div>
    <br />
</div>

<div class="item-views accordion accordion-type-block with_tabs image_left faq">
    <div class="tabs">
        <ul class="nav nav-tabs">
            <? $it = 0; ?>
            <? foreach($sections as $key => $values): ?>
            <?
            if($it == 0) {
                $key_active = $key;
            }
            ?>
            <li class="<?=($it == 0) ? 'active' : '';?>">
                <a data-toggle="tab" href="#bx_3218110189_<?=$key;?>"><?=$values;?></a>
            </li>
            <? $it++; ?>
            <? endforeach; ?>
        </ul>
        <div itemscope itemtype="https://schema.org/Question" class="tab-content">
            <? foreach($elementsTabs as $key => $elements): ?>
            <div id="bx_3218110189_<?=$key;?>" class="tab-pane<?=($key_active == $key)?' active':'';?>">
                <? foreach($elements as $sub_keys => $props): ?>
                <? if($props[2] == 1): ?>
                <div class="accordion-type-1">
                    <div class="item wti" id="<?=$sub_keys;?>">
                        <div class="accordion-head accordion-close" data-toggle="collapse" data-parent="#accordion<?=$key;?>">
                            <span itemprop="name">
                                <?=$props[0];?>
                                <i class="fa fa-angle-down"></i>
                            </span>
                        </div>
                        <div class="panel-collapse collapse">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div itemprop="acceptedAnswer" itemscope itemtype="http://schema.org/Answer" class="text">
                                            <meta itemprop="upvoteCount" content="1" />
                                            <div class="previewtext" itemprop="text">
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
            </div>
            <? endforeach; ?>

        </div>
    </div>
    <div class="module-pagination">
        <div class="nums" data-tabs="<?=$key_active?>">
            <? if($paginationTabs[$key_active] > 1): ?>
            <ul class="flex-direction-nav">
                <li class="flex-nav-next">
                    <a href="javascript:;" data-page="2" class="flex-next"></a>
                </li>
            </ul>
            <? endif; ?>
            <? for($i = 1; $i <= $paginationTabs[$key_active]; $i++): ?>
                <?=($i == 1) ? "<span class=\"cur\">{$i}</span>" : "<a href=\"javascript:;\" data-page=\"{$i}\" class=\"dark_link\">{$i}</a>"; ?>
            <? endfor; ?>
        </div>
    </div>
</div>


<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>