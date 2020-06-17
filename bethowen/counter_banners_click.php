<?php


require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

Bitrix\Main\Loader::includeModule("iblock");

//select data - this we select url and names

$claster = new \Bethowen\Helpers\FasterClaster('story_banners');

$name_slider = $claster->setSection($_REQUEST['iblock']);

$key = sha1($_REQUEST['id_item']);

if($claster->getData($key)) {
    $data = $claster->getData($key);
    //protect empty
    if(empty($data[2]) || empty($data[1])) {
        $claster->setDataNew($_REQUEST['id_item']);
    }
} else {
    $claster->setDataNew($_REQUEST['id_item']);
    $data = $claster->getData($key);
}

$name_element = ($data[1] . " " . date('Y.m.d'));

//try select data if exists
$data_element_exist = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array('IBLOCK_ID' => IBLOCK_CLICK, 'NAME' => $name_element),
    false,
    false,
    array('ID', 'PROPERTY_CLICK_COUNT')
);

$data_count = $data_element_exist->Fetch();

if($data_count != false) {

    $count_click = intval($data_count['PROPERTY_CLICK_COUNT_VALUE']);
    $count_click += 1;

    $out = CIBlockElement::SetPropertyValueCode($data_count['ID'], 'CLICK_COUNT', $count_click);

} else {
    $count_click = 1;

    $dataIBlock = new CIBlockElement();

    /*map prop*/

    $prop_correspond = [];

    $properties = CIBlockProperty::GetList(
        array("sort" => "asc"),
        array("ACTIVE" => "Y", "IBLOCK_ID" => IBLOCK_CLICK)
    );

    while ($prop_fields = $properties->Fetch()){
        $prop_correspond[$prop_fields['CODE']] = $prop_fields['ID'];
    }

    $PROP[$prop_correspond['CLICK_COUNT']] = $count_click;
    $PROP[$prop_correspond['NAME_ROOT']] = $name_slider;
    $PROP[$prop_correspond['DATE_CLICK']] = \Bitrix\Main\Type\DateTime::createFromTimestamp(time());
    $PROP[$prop_correspond['URL_FOLLOW']] = $data[2];

    $arLoadProductArray = array(
        "IBLOCK_ID"      => IBLOCK_CLICK,
        "NAME"           => $name_element,
        "ACTIVE"         => "Y",
        "PROPERTY_VALUES"=> $PROP,
    );

    $element_id = $dataIBlock->Add($arLoadProductArray);

}

echo json_encode(['follow' => $data[2]]);
