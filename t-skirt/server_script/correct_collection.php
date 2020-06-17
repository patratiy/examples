<?php

ini_set('error_reporting', E_ERROR);
ini_set('display_errors', 1);
$_SERVER["DOCUMENT_ROOT"] = "/home/t/tskirt/public_html";
require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

define("IBLOCK_CATALOG", "8");
define("IBLOCK_OFFERS", "9");

CModule::IncludeModule('iblock');

function arrToStrstrip($arr) {
    $unic_arr = array();
    $strip = "";

    foreach($arr as $k => $v) {
        if (!in_array($v, $unic_arr)) {
            array_push($unic_arr, $v);
            $strip .= $v . ",";
        }
    }

    $str_c = substr($strip, 0, strlen($strip) - 1);

    return $str_c;
}

//"267", "234"
$selection_iblock_elements = CIBlockElement::GetList(
    $sortig_module,
    Array("IBLOCK_ID" => IBLOCK_CATALOG, "SECTION_ID" => array("859"), "ACTIVE" => "Y", "CATALOG_AVAILABLE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да" ), //"SECTION_CODE" => $section_name,
    false,
    false,
    Array("*")
);

$data_request_array = array();

$filter_menu_constuction = array();

while($selection_array_ = $selection_iblock_elements->GetNextElement()) {
    $selection_array_p = $selection_array_->GetProperties();
    $selection_array_f = $selection_array_->GetFields();

    $strip = "";
    $array_collection = $selection_array_p[Collection][VALUE];
    foreach($array_collection as $k => $v) {
        $strip .= $v;
    }

    if(strpos($strip, "овинка") != false) {
        //echo "123";
        $test = array(
            "183" => 113,140
        );
    }
    if(strpos($strip, "перья") != false) {
        //echo "234";
        $test = array(
            "183" => 127,140
        );
    }
    if(strpos($strip, "ыпускной") != false) {
        //echo "345";
        $test = array(
            "183" => 129,140
        );
    }

    if(strpos($strip, "овинка") != false && strpos($strip, "перья") != false) {
        $test = array(
            "183" => 113,127,140
        );
    }
    if(strpos($strip, "ыпускной") != false && strpos($strip, "перья") != false) {
        $test = array(
            "183" => 129,140,127
        );
    }
    if(strpos($strip, "ыпускной") != false  && strpos($strip, "овинка") != false) {
        $test = array(
            "183" => 129,140,113
        );
    }

    if(strpos($strip, "перья") == false && strpos($strip, "овинка") == false && strpos($strip, "ыпускной") == false) {
        //echo "456";
        $test = array(
            "183" => 140
        );
    }

    $arr_prop = array("");
    CIBlockElement::SetPropertyValues(
        $selection_array_f['ID'],
        IBLOCK_CATALOG,
        $test,
        "183"
    );
}

?>