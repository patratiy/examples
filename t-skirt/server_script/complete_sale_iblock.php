<?php

ini_set('error_reporting', E_ERROR);
ini_set('display_errors', 1);

## Script for copy data from REST request MyStore for discount information to infoblock property field

$_SERVER["DOCUMENT_ROOT"] = "/home/t/tskirt/public_html";

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/lib/import_discount_mystore.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/local/lib/highloadbk.php");

define('IBLOCK_CATALOG', '8');
define('IBLOCK_OFFERS', '9');

//this call for automate discount get
$this_test_exemplar = new MyStoreInSale();
$this_test_exemplar->gettingGoods();

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");


$discount_table = new WorkerHLBlock(HLBL_DISCOUNTS);

$discount_table->cleanUp();

$data_discount = array();

function setPropSale($infoBlock, $idElement, $propValue) {
    $id_prop_sale = "";
    switch($infoBlock) {
        case "8":
            $id_prop_sale = "255";
            break;
        case "9":
            $id_prop_sale = "256";
            break;
    }
    CIBlockElement::SetPropertyValues(
        $idElement,
        $infoBlock,
        array(
            $id_prop_sale => intval($propValue),
        ),
        $id_prop_sale
    );
}

function getSpecialPrice($id_offers, $id_price) {
    $price = 0;
    $resPrices = CPrice::GetList(
        array(),
        array(
            "PRODUCT_ID" => $id_offers
            ),
        false,
        false,
        array("ID", "PRICE", "CATALOG_GROUP_ID")
    );

    while ($arr = $resPrices->Fetch()) {
        if ($arr[CATALOG_GROUP_ID] == $id_price) {
            $price = $arr[PRICE];
        }
    }
    return $price;
}

function newPricePush($id_offers, $discount_in, $price) {
    //check if this array
    if (is_array($discount_in)) {
        $discount = $discount_in['discount'];
    }
    else {
        $discount = $discount_in;
    }
    // get price for offer with ID
    $resPrices = CPrice::GetList(
        array(),
        array(
            "PRODUCT_ID" => $id_offers
        ),
        false,
        false,
        array("ID", "PRICE", "CATALOG_GROUP_ID")
    );

    $price_1 = 0;
    $price_2 = 0;

    $arr_prices = array();
    while ($arr = $resPrices->Fetch()) {
        $arr_prices[$arr[ID]] = array("price" => $arr[PRICE], "id_price" => $arr[CATALOG_GROUP_ID]);
        switch($arr[CATALOG_GROUP_ID]) {
            case "1":
            $price_1 = $arr[PRICE];
            break;
            case "2":
            $price_2 = $arr[PRICE];
            break;
        }
    }

    $need_up_to_date = true;
    //this check if offer need for updated
    //we don't  check if it not have second price and discount = zero - offer have 2 prices discount zero - and price equal
    if((count($arr_prices) == 1 && $discount == 0) ||
       (count($arr_prices) != 1 && $discount == 0 && $price_1 == $price_2)) {
        $need_up_to_date = false;
    }
    if($need_up_to_date) {
        foreach($arr_prices as $key => $val) {
            // if offer get discount in first times
            if (count($arr_prices) == 1) {
                $price_new = round($price - $price * ($discount / 100.0), 0);

                $arFields1 = Array(
                    "PRODUCT_ID" => $id_offers,
                    "CATALOG_GROUP_ID" => 1,
                    "PRICE" => $price_new,
                    "CURRENCY" => "RUB"
                );
                $arFields2 = Array(
                    "PRODUCT_ID" => $id_offers,
                    "CATALOG_GROUP_ID" => 2,
                    "PRICE" => $price,
                    "CURRENCY" => "RUB"
                );

                CPrice::Update($key, $arFields1);
                CPrice::Add($arFields2);
            }
            else {
                //if offer already have second price and now it not in sale list
                if($discount == 0) {
                    $arFields = Array(
                        "PRODUCT_ID" => $id_offers,
                        "CATALOG_GROUP_ID" => $val[id_price],
                        "PRICE" => getSpecialPrice($id_offers, "2"),
                        "CURRENCY" => "RUB"
                    );
                    CPrice::Update($key, $arFields);
                }
                else {
                    $price_new = round($price - $price * ($discount / 100.0), 0);
                    $suit_price = 0;
                    switch($val[id_price]) {
                        case "1":
                        $suit_price = $price_new;
                        break;
                        case "2":
                        $suit_price = $price;
                        break;
                    }
                    $arFields = Array(
                        "PRODUCT_ID" => $id_offers,
                        "CATALOG_GROUP_ID" => $val[id_price],
                        "PRICE" => $suit_price,
                        "CURRENCY" => "RUB"
                    );
                    CPrice::Update($key, $arFields);
                }
            }
        }
    }
}

function getListOffersById($id_goods) {
    $offers = CIBlockPriceTools::GetOffersArray(
        array(
            'IBLOCK_ID' => IBLOCK_CATALOG,
            'HIDE_NOT_AVAILABLE' => 'Y'
        ),
        array($id_goods)
    );

    $arOffersID = array();

    if($offers) {
        foreach ($offers as $offer) {
            $arOffersID[] = $offer['ID'];
        }
    }

    return $arOffersID;
}

function selectOffers($id_goods, $offer_discount, $for_all) {
    //flag if for all goods - $for_all

    //variable - data for HighloadBlock
    $data_discount = array();
    //this select only offers which have count more then 0 (available)
    $arOffersID = getListOffersById($id_goods);
    //tis we select offer list - for use XML_ID for each
    $offersList = CCatalogSKU::getOffersList(
        $id_goods,
        0,
        array(),
        array('XML_ID', 'ID', 'NAME'),
        array()
    );

    foreach($offersList[$id_goods] as $fieldsEl) {

        if(in_array($fieldsEl['ID'], $arOffersID)) {
            if($for_all == false && $offer_discount[$fieldsEl['XML_ID']]['xml_id'] == $fieldsEl['XML_ID']) {
                setPropSale(IBLOCK_OFFERS, $fieldsEl['ID'], $offer_discount[$fieldsEl['XML_ID']]['discount']);
                $price_base = 0;
                if(getSpecialPrice($fieldsEl["ID"], "2") == 0) {
                    $price_base = getSpecialPrice($fieldsEl["ID"], "1");
                }
                else {
                    $price_base = getSpecialPrice($fieldsEl["ID"], "2");
                }

                newPricePush($fieldsEl['ID'], $offer_discount[$fieldsEl['XML_ID']], $price_base);

                //$price_fields = CPrice::GetBasePrice($fieldsEl["ID"]);
                //$price_base = getSpecialPrice($fieldsEl["ID"], "2");
                $data_discount[] = array(
                    "UF_ID_IN_IBLOCK" => intval($fieldsEl["ID"]),
                    "UF_XML_ID" => strval($fieldsEl["XML_ID"]),
                    "UF_NAME" => strval($fieldsEl["NAME"]),
                    "UF_DISCOUNT" => intval($offer_discount[$fieldsEl['XML_ID']]['discount']),
                    "UF_BASE_PRICE" => intval($price_base),
                    "UF_IBLOCK" => intval(IBLOCK_OFFERS)
                );
        }
            if($for_all == true) {
                setPropSale(IBLOCK_OFFERS, $fieldsEl['ID'], $offer_discount);

                $price_base = 0;
                if(getSpecialPrice($fieldsEl["ID"], "2") == 0) {
                    $price_base = getSpecialPrice($fieldsEl["ID"], "1");
                }
                else {
                    $price_base = getSpecialPrice($fieldsEl["ID"], "2");
                }

                newPricePush($fieldsEl['ID'], $offer_discount, $price_base);
            }
        }
    }

    return $data_discount;
}

$keys_goods_in_sale = array_keys($this_test_exemplar->arDiscounts);

//this variable useing for testing get data from CSV

$sale_list_file = [];
$sale_list_file = array_merge($this_test_exemplar->arDiscounts);

//this we check - it data for some reason is empty
if (empty($sale_list_file)) {
    die("list discount is empty");
}

foreach($file as $line) {
    $parts = explode(';', str_replace("\n", "", $line));
    preg_match("/#/", $parts[0], $matches);
    $keys_goods_in_sale[] = (!empty($matches)) ? explode("#", $parts[0])[0] : $parts[0];
    if(!empty($matches)) {
        $sale_list_file[(!empty($matches)) ? explode("#", $parts[0])[0] : $parts[0]] =  array($parts[0] => intval($parts[1]));
    }
    else {
        $sale_list_file[$parts[0]] = intval($parts[1]);
    }
}

$selection_elements = CIBlockElement::GetList(
    Array("ID" => "ASC"),
    Array("IBLOCK_ID" => IBLOCK_CATALOG, "SECTION_ID" => array("267", "234"), "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да", "CATALOG_AVAILABLE" => "Y"), //"SECTION_CODE" => $section_name,
    false,
    false, //"nTopCount" => 1
    Array("SORT", "CODE", "IBLOCK_ID", "ID", "XML_ID", "CATALOG_AVAILABLE", "NAME", "PROPERTY_MIN_PRICE") //"ID", "IBLOCK_ID", "NAME", "DETAIL_PICTURE", "PREVIEW_PICTURE", "PROPERTY_CML2_ARTICLE", "PROPERTY_LIKE", "PROPERTY_ACTIVE", "PROPERTY_MIN_PRICE", "PROPERTY_NAPISANIE_TOVARA", "PROPERTY_SKIRT", "PROPERTY_F_USER"
);

$data_discount = array();

while($selection_array = $selection_elements->Fetch()) {
    //here we process goods, which in sale massive
    if(in_array($selection_array["XML_ID"], $keys_goods_in_sale)) {
        //if is discount going in array we think that have only one element - for one offer of goods
        if(is_array($sale_list_file[$selection_array["XML_ID"]])) {
            //discount apply for special offer
            $massive_discounts = array();
            foreach($sale_list_file[$selection_array["XML_ID"]] as $key => $val) {
               $massive_discounts[$key] = array("xml_id" => $key, "discount" => $val);
            }
            //calculate max price around all offers
            $discount = array();
            foreach($massive_discounts as $element) {
                $discount[] = $element['discount'];
            }
        }
        else {
            $discount = $sale_list_file[$selection_array["XML_ID"]];
        }

        if(is_array($sale_list_file[$selection_array["XML_ID"]])) {
            //$offers_arr = selectOffers($selection_array["ID"], $massive_discounts, false);

            $offers_arr = selectOffers($selection_array["ID"], $massive_discounts, false);
            //add to properties sale in percent value
            setPropSale(IBLOCK_CATALOG, $selection_array['ID'], max($discount));
        }
        else {
            $offers_arr = selectOffers($selection_array["ID"], $discount, true);

            $id_get_offers = getListOffersById($selection_array["ID"]);

            setPropSale(IBLOCK_CATALOG, $selection_array['ID'], $discount);
            $data_discount[] = array(
                "UF_ID_IN_IBLOCK" => intval($selection_array['ID']),
                "UF_XML_ID" => strval($selection_array["XML_ID"]),
                "UF_NAME" => strval($selection_array["NAME"]),
                "UF_DISCOUNT" => intval($discount),
                "UF_BASE_PRICE" => intval(getSpecialPrice($id_get_offers[0], "2")),
                "UF_IBLOCK" => intval(IBLOCK_CATALOG),
            );
        }
        //add new data for highload block
        $data_discount = array_merge($data_discount, $offers_arr);

        //this we add goods to section - SALE
        $db_old_groups = CIBlockElement::GetElementGroups($selection_array["ID"], true);
        $ar_new_groups = array();
        while($ar_group = $db_old_groups->Fetch()) {
            $ar_new_groups[] = $ar_group["ID"];
        }
        if (!in_array("862", $ar_new_groups)) {
            $ar_new_groups[] = "862";
        }
        CIBlockElement::SetElementSection($selection_array["ID"], $ar_new_groups);
    }
    //rapid correction 18.07.2018 12:14:21
    //here we work with other active goods - and we must annulated discount if goods not in sale list (going from MySklad)
    else {

        $id_goods = $selection_array["ID"];

        $arOffersID = getListOffersById($selection_array["ID"]);

        $offersList = CCatalogSKU::getOffersList(
            $id_goods,
            0,
            array(),
            array('XML_ID', 'ID', 'NAME', 'PRICE'),
            array()
        );

        foreach ($offersList[$id_goods] as $fieldsEl) {
            if(in_array($fieldsEl['ID'], $arOffersID)){
                setPropSale(IBLOCK_OFFERS, $fieldsEl['ID'], 0);
                $price_base_ = (getSpecialPrice($fieldsEl["ID"], "2") != 0) ? getSpecialPrice($fieldsEl["ID"], "2") : getSpecialPrice($fieldsEl["ID"], "1");
                newPricePush($fieldsEl['ID'], 0, $price_base_);
            }
        }

        //set zero discount for goods in properties
        setPropSale(IBLOCK_CATALOG, $selection_array['ID'], 0);

        //now we delete goods from section SALE in #8 InfoBlock
        $db_groups = CIBlockElement::GetElementGroups($selection_array["ID"], true);
        $ar_new_groups = array();
        while($ar_group = $db_groups->Fetch()) {
            //temp for Friday
            if ($ar_group["ID"] != "862") {
                $ar_new_groups[] = $ar_group["ID"];
            }
        }

        CIBlockElement::SetElementSection($selection_array["ID"], $ar_new_groups);
    }
}

for($j = 0; $j < count($data_discount); $j++) {
    //temp for black Friday
    $discount_table->pushNewData($data_discount[$j]);
}

?>
