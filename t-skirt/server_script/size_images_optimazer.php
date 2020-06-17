<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

## Script for reduce size images for detail and preview images ImaginWeb 2018 Copyright

$_SERVER["DOCUMENT_ROOT"] = "/home/t/tskirt/public_html";
include_once($_SERVER["DOCUMENT_ROOT"] . "/local/lib/functions.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('iblock');

$elements = CIBlockElement::GetList(
    array("SORT" => "ASC"),
    array("IBLOCK_ID" => "8", "ACTIVE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да", "CATALOG_AVAILABLE" => "Y"),
    false,
    false,
    array("*")
);

$counter_goods = 0;

while($poly = $elements->GetNextElement()) {
    $field = $poly->GetFields();
    $prop = $poly->GetProperties();
    $array_file = [];

    //echo $field[ID] . "\n";

    $file_img_up = new FileWorker();

    if($field[PREVIEW_PICTURE] != '') {
        $array_file[$field[PREVIEW_PICTURE]] = 'prev';
    }
    if($field[DETAIL_PICTURE] != '') {
        $array_file[$field[DETAIL_PICTURE]] = 'detail';
    }

    $count_tmp = 0;

    foreach ($prop[MORE_PHOTO][VALUE] as &$value) {
        $array_file[$value] = 'more_photo';
        $count_tmp++;
    }

    $file_img_up->batchProcessor($field[ID], $array_file);

    $counter_goods++;
}

echo "Checked iblock elements: " . $counter_goods;

?>