<?php

//
// Скрипт предназначен для автоматических коррекций после синхронизации с "Мой Склад" на CRON, ImaginWeb Copyright 2018
// version 1.0.1

$_SERVER["DOCUMENT_ROOT"] = "/home/t/tskirt/public_html";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

//for debugging proc
ini_set('error_reporting', E_ERROR);
ini_set('display_errors', 1);

CModule::IncludeModule("iblock");
CModule::IncludeModule('catalog');

use Bitrix\Main\Loader;

Loader::includeModule("highloadblock");


use Bitrix\Main\Entity;

define('IBLOCK_OFFERS', '9');
define('HLBL_COLOR', '1');
define('HLBL_SYNC_GOODS', '3');

class WorkerHLBlock
{
    private $hlbl;
    private $entity;
    public $count_lines;

    public function __construct($id_hlbl) {
        $this->hlbl = $id_hlbl;
        $this->determinateEntity();
        $entity_data = $this->entity->getDataClass();
        $this->count_lines = $entity_data::getCount();
    }

    private function determinateEntity() {
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($this->hlbl)->fetch();
        $this->entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
    }

    public function pushNewData($push_data) {
        //$this->determinateEntity();
        $entity_data = $this->entity->getDataClass();
        $result = $entity_data::add($push_data);
    }

    public function selectData($select_fields) {
        //$this->determinateEntity();
        $entity_data = $this->entity->getDataClass();
        $rsData = $entity_data::getList(array(
           "select" => $select_fields,
           "order" => array(),
           "filter" => array()
        ));

        return $rsData;
    }

    public function deleteRecord($id_rec) {
        //$this->determinateEntity();
        $entity_data_class = $this->entity->getDataClass();
        $entity_data_class::Delete($id_rec);
    }

    public function cleanUp() {
        $select_fields = Array("ID");
        $dataSelect = $this->selectData($select_fields);
        while($data = $dataSelect->Fetch()) {
            $this->deleteRecord($data[ID]);
        }
    }

    public function upToDate($id_update, $up_date) {
        /*$up_date = array(
            '' => ''
        );*/
        $entity_data = $this->entity->getDataClass();
        $result = $entity_data::update($id_update, $up_date);
    }
}

class WorkerInfoBlock
{
    public $select_iblock_elements;

    private $ib_list_sections;
    private $select_prop;

    private $color;
    private $clothes_size;
    public $arrSizesCode;
    public $arrColors;
    private $element;
    private $size_prop;
    private $color_prop;

    private $countOffUpdate;
    private $willBeUpdate;
    private $selection_spec;
    private $entity_color;

    public function __construct() {
        $this->select_prop = array();
        $this->selection_spec = false;
        $select_hlbl = array("UF_NAME", "UF_XML_ID");
        // выделяем все данные из HL блока по текущим цветам записываем в массив для сравнения с Характеристиками
        $this->entity_color = new WorkerHLBlock(HLBL_COLOR);
        $colorsArr = $this->entity_color->selectData($select_hlbl);

        while($data = $colorsArr->Fetch()){
            $this->arrColors[strtolower($data[UF_NAME])] = $data[UF_XML_ID];
        }

        $this->arrSizesCode = array(
            "M/L" => "26",
            "S" => "18",
            "XS" => "17",
            "One size" => "22",
            "XS/S" => "25",
            "L" => "20",
            "M" => "19",
            "XL" => "21"
        );
    }

    private function gencode($word) {

        $word .= rand();
        $unic = '';
        $xml_code = substr(base64_encode($word), strlen(base64_encode($word)) - 15, 15);
        for($i = 0; $i < strlen($xml_code); $i++) {
            $unic .= $xml_code[rand(0, 14)];
        }

        return str_ireplace('=', 'x', $unic);
    }

    protected function blockSelectionElements($id_ib_off) {
        unset($this->select_iblock_elements);
        $this->select_iblock_elements = CIBlockElement::GetList(
            Array("timestamp_x" => "desc"),
            Array("IBLOCK_ID" => $id_ib_off, "ACTIVE" => "Y", "CATALOG_AVAILABLE" => "Y"), 
            false,
            false, 
            Array()
        );
    }

    public function blockSelectionSpecElements($filter) {
        unset($this->select_iblock_elements);
        $this->select_iblock_elements = CIBlockElement::GetList(
            Array("timestamp_x" => "desc"),
            $filter,
            false,
            false,
            Array()
        );
        $this->selection_spec = true;
    }

    public function correctOffers($id_ib_off) {
        unset($this->countOffUpdate);
        $this->blockSelectionElements($id_ib_off);

        $co = 0;
        $allow = array("14749","14759", "31131", "8334"); // test
        while ($offer = $this->select_iblock_elements->Fetch()) {
            $this->select_prop = CIBlockElement::GetProperty(
                $id_ib_off,
                $offer['ID'],
                Array("SORT" => "ASC"),
                Array("ACTIVE" => "Y", "ID" => Array("114", "129", "130"))
            );
            $this->copyValuesCharact();
            $this->willBeUpdate = False;
            $this->setPropOffers($id_ib_off, $offer['ID'], "129", $this->clothes_size);
            $this->setPropOffers($id_ib_off, $offer['ID'], "130", $this->color);//"8DOdnnfX"
            unset($this->size_prop);
            unset($this->color_prop);
            if($this->willBeUpdate) {
                $this->countOffUpdate++;
            }
            usleep(10000);
            $co++;
            
        }
        $mess =
            "Общее число проверенных записей торговых предложений на предмет обновления (Размер, Цвет): " . $co;

        return $mess;
    }

    public function deactiveOffersLess1($id_ib_off) {
        $this->blockSelectionElements($id_ib_off);
        $this->element = new CIBlockElement;
        $count_off_less_3 = 0;
        $co = 0;
        $allow = array("8285", "31129", "31131", "8334");
        while ($offer = $this->select_iblock_elements->Fetch()) {

            if($this->getQuantityOffer($offer[ID]) < 1) {
                $this->deactivateOffer($offer[ID]);
                $count_off_less_3++;
            }

            if($this->getQuantityOffer($offer[ID]) > 0) {
                $this->activateOffer($offer[ID]);
            }
            $co++;
            
        }

         $mess =
            "Общее число проверенных/обновленных записей торговых предложений, с остатком меньше '3': " . $co . " / " . $count_off_less_3;
        return $mess;
    }

    public function checkNewOffers($id_ib_off) {
        if(!$this->selection_spec) {
            $this->blockSelectionElements($id_ib_off);
        }

        $this->element = new CIBlockElement;

        $hlblworker = new WorkerHLBlock(HLBL_SYNC_GOODS);
        $filter = array(
            "UF_EXT_CODE_OFF", "UF_CHECKED"
        );
        $selection = $hlblworker->selectData($filter);
        $store_in_site = array();
        while ($fetched_data = $selection->Fetch()) {
            $store_in_site[$fetched_data[UF_EXT_CODE_OFF]] = $fetched_data[UF_CHECKED];
        }
        $ext_codes = array_keys($store_in_site);

        // выделяем все данные из HL блока по текущим цветам записываем в массив для сравнения с Характеристиками
        $co = 0;
        $count_new_off = 0;
        $allow = array("8285", "31129", "31131", "8334");
        while ($offer = $this->select_iblock_elements->Fetch()) {

            // проверяем, после синхронизации налицие новых ТП, в случае появления таких добавляем как неактивные
            if(!in_array($offer[XML_ID], $ext_codes)) {
                $data = array(
                    "UF_DATE_LAST_CHECK"=>date('d.m.Y H:i:s', time()),
                    "UF_EXT_CODE_OFF"=>$offer['XML_ID'],
                    "UF_CHECKED"=>false
                );
                $hlblworker->pushNewData($data);
                // деактивируем новую запись если она новая (отсутствует в HLBLOCK)
                $this->deactivateOffer($offer[ID]);
                $count_new_off++;
            }
            else {
                if ($store_in_site[$offer['XML_ID']] == 0) {
                    $this->deactivateOffer($offer[ID]);
                }
            }
            $co++;
            
        }
        unset($store_in_site);

        $mess =
            "Общее число просмотренных/новых торговых предложений: " . $co ." / ".$count_new_off;
        return $mess;
    }

    public function setPropOffers($id_ib_off, $id_off, $code, $val) {

        switch($code) {
            case "129":
            if (isset($this->size_prop)) {//$fields_prop['VALUE']
                continue;
            }
            else {
                if (isset($this->clothes_size)) {
                    CIBlockElement::SetPropertyValueCode(
                      $id_off,
                      $code,
                      $val
                    );
                    $this->willBeUpdate = True;
                }
            }
            break;
            case "130":

            if (isset($this->color)) {
                CIBlockElement::SetPropertyValueCode(
                    $id_off,
                    $code,
                    $val
                );
                $this->willBeUpdate = True;
            }

            break;
        }
    }

    protected function copyValuesCharact() {
        $size = "";
        $color = "";
        while($prop = $this->select_prop->Fetch()){
            if ($prop["DESCRIPTION"] == 'размер') {
                $size = str_replace("  ", " ", $prop['VALUE']);
                if (!isset($this->size_prop)) {
                    $this->clothes_size = $this->arrSizesCode[$size];
                }
                else {
                    unset($this->clothes_size);
                }
            }
            if ($prop["DESCRIPTION"] == 'цвет') {
                $color = strtolower($prop['VALUE']);
                $keys = array_keys($this->arrColors);
                $color_name = strtoupper(substr($color, 0, 1)) . substr($color, 1, strlen($color) - 1);

                if (!in_array($color, $keys)) {
                    $code_xml = $this->gencode($color);
                    $data = array(
                      "UF_SORT"=>'100',
                      "UF_NAME"=>$color_name,
                      "UF_XML_ID"=>$code_xml
                    );
                    $this->entity_color->pushNewData($data);
                    $this->arrColors[$color] = $code_xml;

                    $this->color = $this->arrColors[$color];
                }
                else {
                    $this->color = $this->arrColors[$color];
                }
            }
            if($prop["CODE"] == "SIZES_CLOTHES") {
                if(isset($prop["VALUE_ENUM"])) {
                    $this->size_prop = $prop["VALUE_ENUM"];
                    unset($this->clothes_size);
                }
            }
            if($prop["CODE"] == "COLOR_REF") {
                if(isset($prop["VALUE_ENUM"])) {
                    $this->color_prop = $prop["VALUE_ENUM"];
                    unset($this->color);
                }
            }
        }

        unset($this->select_prop);
    }

    public function getQuantityOffer($id_off) {
        $cat_goods = CCatalogProduct::GetList(
            Array("sort" => "asc"),
            Array("ACTIVE"=>"Y", "ID"=>$id_off),
            false,
            false,
            Array()
        );
        $fields = $cat_goods->Fetch();
        return $fields["QUANTITY"];
    }

    public function deactivateOffer($id_off){
        $de_active = array(
            "ACTIVE" => "N"
        );

        $this->element->Update($id_off, $de_active);
    }

    public function activateOffer($id_off){
        $de_active = array(
            "ACTIVE" => "Y"
        );

        $this->element->Update($id_off, $de_active);
    }
}
// выполняется на CRON копируются значения из Характеристик в свойства
if ($argv[1] == 'copyprop') {
    $selection = new WorkerInfoBlock();
    $mess = $selection->correctOffers(IBLOCK_OFFERS);
}
// выполняется на CRON проверяется количество активных и доступных ТП, в случа значения меньше 3 - деактивируется
// from 18/06/2018 - remove this case - not deact less then 1
if ($argv[1] == 'checkless3') {
    $selection = new WorkerInfoBlock();
    $mess = $selection->deactiveOffersLess1(IBLOCK_OFFERS);
}
// выполняется на CRON проверяются новые ТП. Новые (отсутсвующие) в HLBL 3 ТП помечаются как не проверенные, и деактивируются
if ($argv[1] == 'checknewoff') {
    $filter = array("IBLOCK_ID" => IBLOCK_OFFERS);
    $selection = new WorkerInfoBlock();
    $selection->blockSelectionSpecElements($filter);
    $mess = $selection->checkNewOffers(IBLOCK_OFFERS);
}
// локика аналогична вышеперечисленным блокам, команды вызываются через AJAX по кнопке (ручная активация скрипта)
if(isset($_GET['action']) && $_GET['action'] == "update_prop") {
    $selection = new WorkerInfoBlock();
    $mess = $selection->correctOffers(IBLOCK_OFFERS);
    echo "{";
    header("Content-Type: application/json");
    echo "\"mess\":\"" . $mess . "\"";
    echo "}";
}

if(isset($_GET['action']) && $_GET['action'] == "checknew") {
    $filter = array("IBLOCK_ID" => IBLOCK_OFFERS);
    $selection = new WorkerInfoBlock();
    $selection->blockSelectionSpecElements($filter);
    $mess = $selection->checkNewOffers(IBLOCK_OFFERS);
    echo "{";
    header("Content-Type: application/json");
    echo "\"mess\":\"" . $mess . "\"";
    echo "}";
}

?>