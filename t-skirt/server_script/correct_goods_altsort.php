<?php

ini_set('error_reporting', E_ERROR);
ini_set('display_errors', 1);
$_SERVER["DOCUMENT_ROOT"] = "/home/t/tskirt/public_html";
//require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/local/lib/highloadbk.php");

define("IBLOCK_CATALOG", "8");
define("IBLOCK_OFFERS", "9");
define("HLBL_COLOR", "1");

CModule::IncludeModule('iblock');

$selection_iblock_elements = CIBlockElement::GetList(
    $sortig_module,
    Array("IBLOCK_ID" => IBLOCK_CATALOG, "SECTION_ID" => array("267", "234"), "ACTIVE" => "Y", "CATALOG_AVAILABLE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да" ), //"SECTION_CODE" => $section_name,
    false,
    false,
    Array("ID")
);


$f_hand = fopen('/home/t/tskirt/public_html/server_script/list_alt_sort_backup_1805.txt', 'r');

$arr_sort = array();

$ix = 0;

while ($line = fgets($f_hand)) {
    $id = explode(',', $line)[0];
    $sort_1 = explode(',', $line)[1];
    $sort_2 = explode(',', $line)[2];
    $sort_3 = str_replace("\r\n", "", explode(',', $line)[3]);

    $arr_sort[$id] = array(
        "247"=>$sort_1,
        "248"=>$sort_2,
        "249"=>$sort_3
    );

    $ix++;
}


while($element = $selection_iblock_elements->Fetch()) {

    CIBlockElement::SetPropertyValues(
        $element['ID'],
        IBLOCK_CATALOG,
        array(
            "247" => $arr_sort[$element['ID']]['247'],
        ),
        "247"
    );
    CIBlockElement::SetPropertyValues(
        $element['ID'],
        IBLOCK_CATALOG,
        array(
            "248" => $arr_sort[$element['ID']]['248'],
        ),
        "248"
    );
    CIBlockElement::SetPropertyValues(
        $element['ID'],
        IBLOCK_CATALOG,
        array(
            "249" => $arr_sort[$element['ID']]['249'],
        ),
        "249"
    );

}

echo "finish\n";

?>