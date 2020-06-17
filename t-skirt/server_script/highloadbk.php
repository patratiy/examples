#!/usr/bin/php
<?php

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
    
    public function deleteAll() {
        $select_fields = Array("ID");
        $dataSelect = $this->selectData($select_fields);
        while($data = $dataSelect->Fetch()) {
            $this->deleteRecord($data[ID]);
        }
    }
}



$select_iblock_elements = CIBlockElement::GetList(
    Array("ID" => "DESC"),
    Array("IBLOCK_ID" => "9", "ACTIVE" => "Y"), // "CATALOG_AVAILABLE" => "Y" //DELET ID FROM FILTER ON REAL SERVER
    false,
    false, //"nTopCount" => 1
    Array()//"ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "CODE")
);

$fulfill_hlbl = new WorkerHLBlock(HLBL_SYNC_GOODS);

/*while ($offer = $select_iblock_elements->Fetch()) {
    $data = array(
      "UF_DATE_LAST_CHECK"=>date('d.m.Y H:i:s', time()),
      "UF_EXT_CODE_OFF"=>$offer['XML_ID'],
      "UF_CHECKED"=>"Y"
    );

    $fulfill_hlbl->pushNewData($data);
}*/

$cat_goods = CCatalogProduct::GetList(
    Array("sort" => "asc"),
    Array("ACTIVE"=>"Y", "ID"=>"31129"),
    false, 
    false,
    Array()
);

$fields = $cat_goods->Fetch();

$select_prop = CIBlockElement::GetProperty(
    "9", 
    $fields['ID'], 
    Array("SORT" => "ASC"),
    Array("ACTIVE" => "Y", "ID" => Array("114", "129", "130"))
);

while($prop = $select_prop->Fetch()){
    echo var_dump($prop) . "\n";
}

//echo $fields["QUANTITY"] . "\n";

$de_active = array(
    "ACTIVE" => "N"
);

//$element = new CIBlockElement;

//$element->Update(31131, $de_active);

/*$data = array(
      "UF_DATE_LAST_CHECK"=>"24.04.2018 10:10:10",
      "UF_EXT_CODE_OFF"=>"12124512",
      "UF_CHECKED"=>"Y"
    );
$fulfill_hlbl->pushNewData($data);*/



/*echo "<pre>";

print_r($test_arr);

echo "</pre>";*/

/*
CCatalogSKU::getExistOffers(
 array productID,
 int iblockID = 0
)
*/

?>