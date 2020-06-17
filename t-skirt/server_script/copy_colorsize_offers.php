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


$selection_iblock_elements = CIBlockElement::GetList(
    $sortig_module,
    Array("IBLOCK_ID" => IBLOCK_CATALOG, "SECTION_ID" => array("267", "234"), "INCLUDE_SUBSECTIONS" => "Y", "CATALOG_AVAILABLE" => "Y", "ACTIVE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да" ), //"SECTION_CODE" => $section_name,
    false,
    false,
    Array("ID") 
);

$enter = 0;

while($selection_array_f = $selection_iblock_elements->Fetch()) {
    $enter++;

    ## ---------- OFFERS ------------
    // use offers even if not available
    $offers = CIBlockPriceTools::GetOffersArray(
        array(
            'IBLOCK_ID' => 8,
            'HIDE_NOT_AVAILABLE' => 'Y'
        ),
        array($selection_array_f['ID'])
    );

    $arOffersID = array();

    if($offers){
        foreach ($offers as $offer) {
            $arOffersID[] = $offer['ID'];
        }
    }

    $offersList = CCatalogSKU::getOffersList(
        $selection_array_f[ID],
        false,
        false, //array( 'CATALOG_AVAILABLE' => 'Y' ),
        array('PROPERTY_COLOR_REF', 'PROPERTY_SIZES_CLOTHES')
    );

    $color = array();
    $size = array();
    $price = array();

    if($offersList[$selection_array_f[ID]]){
        foreach ($offersList[$selection_array_f[ID]] as $fieldsEl) {

            if(in_array($fieldsEl['ID'], $arOffersID)){
                $arfieldsEl = array(
                    'COLOR' => $fieldsEl['PROPERTY_COLOR_REF_VALUE'],
                    'SIZE' => $fieldsEl['PROPERTY_SIZES_CLOTHES_VALUE']
                );

                $color[] = $arfieldsEl['COLOR'];
                $size[] = $arfieldsEl['SIZE'];

                $key = array_search('', $size);
                if ($key != '')
                    array_splice($size, $key, 1);

                $arPrice = GetCatalogProductPrice($fieldsEl["ID"], 1);
                $price[] = FormatCurrency($arPrice['PRICE'], $arPrice["CURRENCY"]);
            }
        }
    }

    $arcolor = array_values(array_unique($color));
    $arsize = array_values(array_unique($size));
    $priceMin = min(array_values(array_unique($price)));

    $size_strip = arrToStrstrip($arsize);
    $color_strip = arrToStrstrip($arcolor);

    CIBlockElement::SetPropertyValues(
        $selection_array_f['ID'],
        IBLOCK_CATALOG,
        array(
            "251" => $size_strip,
        ),
        "251"
    );

    CIBlockElement::SetPropertyValues(
        $selection_array_f['ID'],
        IBLOCK_CATALOG,
        array(
            "250" => $color_strip,
        ),
        "250"
    );

    CIBlockElement::SetPropertyValues(
        $selection_array_f['ID'],
        IBLOCK_CATALOG,
        array(
            "159" => $priceMin,
        ),
        "159"
    );

    //echo $size_strip . " :: " . $color_strip . " :: " . $priceMin ."\n";
}


unset($selection_iblock_elements);

$selection_iblock_elements = CIBlockElement::GetList(
    $sortig_module,
    Array("IBLOCK_ID" => IBLOCK_CATALOG, "SECTION_ID" => array("267", "234"), "INCLUDE_SUBSECTIONS" => "Y", "CATALOG_AVAILABLE" => "N", "ACTIVE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да" ), //"SECTION_CODE" => $section_name,
    false,
    false,
    Array("ID")
);

//$enter = 0;

while($selection_array_f = $selection_iblock_elements->Fetch()) {
    $enter++;

    ## ---------- OFFERS ------------
    // use offers even if not available
    $offers = CIBlockPriceTools::GetOffersArray(
        array(
            'IBLOCK_ID' => 8
            /*'HIDE_NOT_AVAILABLE' => 'Y'*/
        ),
        array($selection_array_f['ID'])
    );

    $arOffersID = array();

    if($offers){
        foreach ($offers as $offer) {
            $arOffersID[] = $offer['ID'];
        }
    }

    $offersList = CCatalogSKU::getOffersList(
        $selection_array_f[ID],
        false,
        false, //array( 'CATALOG_AVAILABLE' => 'Y' ),
        array('PROPERTY_COLOR_REF', 'PROPERTY_SIZES_CLOTHES')
    );

    $price = array();

    if($offersList[$selection_array_f[ID]]){
        foreach ($offersList[$selection_array_f[ID]] as $fieldsEl) {

            if(in_array($fieldsEl['ID'], $arOffersID)){

                $arPrice = GetCatalogProductPrice($fieldsEl["ID"], 1);
                $price[] = FormatCurrency($arPrice['PRICE'], $arPrice["CURRENCY"]);
            }
        }
    }

    $priceMin = min(array_values(array_unique($price)));

    CIBlockElement::SetPropertyValues(
        $selection_array_f['ID'],
        IBLOCK_CATALOG,
        array(
            "159" => $priceMin,
        ),
        "159"
    );
}

echo $enter;

?>