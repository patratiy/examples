<?php

// Libre work with HighLoad block ImaginWeb 2018 Copyright
// 1.0.1

//$_SERVER["DOCUMENT_ROOT"] = "/home/t/tskirt/public_html";
//require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

//for debugging proc
ini_set('error_reporting', E_ERROR);
ini_set('display_errors', 1);

use Bitrix\Main\Loader;

Loader::includeModule("highloadblock");

use Bitrix\Main\Entity;

define('HLBL_COLOR', '1');
define('HLBL_SYNC_GOODS', '3');
define('HLBL_CATALOG', '4');
define('HLBL_MENU', '5');
define('HLBL_DISCOUNTS', '6');
define("HLBL_STRUCTURE", "7");
define("HLBL_COUPON", "8");
define("HLBL_UNIC_BUYER", "9");

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

    public function selectDataSortFilt($select_fields, $sort, $filter) {
        //$this->determinateEntity();
        $entity_data = $this->entity->getDataClass();
        $rsData = $entity_data::getList(array(
           "select" => $select_fields,
           "order" => $sort,
           "filter" => $filter
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

?>