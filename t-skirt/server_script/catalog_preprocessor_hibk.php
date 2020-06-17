<?php

## Code name "RapidCatalog" - this backend script on CRON for accomulate data from info blocks to HighLoad block
## version 1.0.0 ImaginWeb 2018 Copyright

ini_set('error_reporting', E_ERROR);
ini_set('display_errors', 1);
$_SERVER["DOCUMENT_ROOT"] = "/home/t/tskirt/public_html";
//require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/local/lib/highloadbk.php");

define("IBLOCK_CATALOG", "8");
define("IBLOCK_OFFERS", "9");
define("HLBL_COLOR", "1");

$hlbl_catalog = new WorkerHLBlock(HLBL_CATALOG);
$hlbl_menu = new WorkerHLBlock(HLBL_MENU);


$hlbl_catalog->cleanUp();
$hlbl_menu->cleanUp();


CModule::IncludeModule("iblock");
CModule::IncludeModule('catalog');

/* now this use spec filter diffrent with Bitrix */
function selectChildNodesByParId($id, $data_array) {
    $arrRes = array();
    $i = 0;
    foreach ($data_array as $key_ => $val_) {
        if ($id == $val_[parent_id] && $val_[active] == 'Y') {
            $i++;
            $arrRes[$i] = $data_array[$key_];
        }
    }
    return $arrRes;
}

function getNameIDSecByID($id, $arr) {
    $arrRes = array();
    foreach ($arr as $key_ => $val_) {
        if ($id == $val_['id']) {
            $arrRes = $arr[$key_];
            break;
        }
    }
    return $arrRes;
}

function selectNameSecById($id, $arr) {
    $name;
    foreach ($arr as $key => $val) {
        if ($id == $val[id]) {
            $name = $val[name_code];
            break;
        }
    }
    return $name;
}

function selectNameAndParentById($id, $arr) {
    $result = array();
    foreach ($arr as $key => $val) {
        if ($id == $val[id]) {
            $result = Array($val[name_code], $val[parent_name_code]);
            break;
        }
    }
    return $result;
}

function indexSectionPos($url, $code_name) {
    $temp = explode('/', $url);
    $i = 0;
    foreach($temp as $element) {
        if ($element != "") {
            $i++;
            if ($element == $code_name) {
                break;
            }
        }
    }
    return $i;
}

function selectAllChildID($code_name, $arr) {
    $result_select_ids = array();
    foreach ($arr as $key => $val) {
        if ($code_name == $val[name_code]) {
            $index = indexSectionPos($val[url], $val[name_code]);
            switch($index) {
                case 2:
                    $arr_ = selectChildNodesByParId($val[id], $arr);
                    foreach($arr_ as $k => $v) {
                        $arr__ = selectChildNodesByParId($v[id], $arr);
                        foreach($arr__ as $k_ => $v_) {
                            array_push($result_select_ids, $v_[id]);
                        }
                    }
                    break;
                case 3:
                    $arr_ = selectChildNodesByParId($val[id], $arr);
                    foreach($arr_ as $k => $v) {
                        array_push($result_select_ids, $v[id]);
                    }
                    break;
                case 4:
                    array_push($result_select_ids, $val[id]);
                    break;
                default:
                break;
            }
        }
    }
    return $result_select_ids;
}

function correct_url_sell_prod($url_full) {
    $temp = explode("/", $url_full);
    $result = '/' . $temp[1] . '/' . $temp[2] . '/' . $temp[count($temp) - 2] . '/';
    return $result;
}

function selectSectionsByParentId($id_level_1, $in_arr) {
    $temp_arr = array();
    foreach($in_arr as $k => $v) {
        if ($v[IBLOCK_SECTION_ID] == $id_level_1) {
            $temp_arr[] = array("name" => $v[NAME], "code" => $v[CODE], "id" => $k);
        }
    }
    return $temp_arr;
}

function priceFormater($price) {
    if(strlen($price) > 3) {
        $sub_2th = substr($price, 0, strlen($price) - 3);
        $sub_1th = substr($price, strlen($price) - 3, strlen($price));
        $f_pr = $sub_2th . ' ' . $sub_1th . ' ₽';
    }
    else {
        $f_pr = $price . ' ₽';
    }
    return $f_pr;
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

/*--------------*/

$ib_list_1c_catalog = CIBlockSection::GetList(
    Array("SORT" => "ASC"),
    Array(
        "IBLOCK_ID" => IBLOCK_CATALOG,
        "ACTIVE" => "Y",
        "DEPTH_LEVEL" => Array("1", "2") // выделяем только верхний и его дочерний уровень
    ),
    true,
    Array('ID','NAME','SECTION_PAGE_URL','IBLOCK_SECTION_ID', 'SORT', 'ACTIVE', 'UF_ACTIVE', 'CODE'),
    false
);

$array_section = array();

#echo "<pre>";
while($section_selection = $ib_list_1c_catalog->Fetch()) {
    /* выделяем только то меню, которое помечено как показывать в меню */
    if($section_selection['UF_ACTIVE'] == 1) {
        $array_section[$section_selection['ID']] = array(
            "CODE" => $section_selection['CODE'],
            "NAME" => $section_selection['NAME'],
            "IBLOCK_SECTION_ID" => $section_selection['IBLOCK_SECTION_ID']
        );
    }
}

$array_sections = array();

class MenuDataCatGenerate {
    private $selection_elements;
    private $root_id;
    protected $data_catalog;
    protected $menu_catalog;

    public $filters_name;
    public $filter_section;
    public $collection_code;
    public $collection_code_name;
    public $root_section;

    public function __construct($sect_id) {
        unset($this->selection_elements);
        $this->root_id = $sect_id;
        $this->data_catalog = array();
        $this->selectData();

        $this->root_section = array(
            "267" => "odezhda_1",
            "234" => "aksessuary_1",
            "862" => "sale"
        );

        $this->selectStructure($sect_id);
    }

    private function selectStructure($sect_id) {
        $this->filters_name = [];
        $this->filters_sort = [];
        $this->filter_section = [];

        $this->collection_code = [];
        $this->collection_code_name = [];

        $hlbl_structure = new WorkerHLBlock(HLBL_STRUCTURE);
        $select_hlbl = array("UF_ROOT_SEC", "UF_SEC_CODE", "UF_SORT", "UF_SEC_NAME", "UF_CATEGORY", "UF_PRINT_NAME", "UF_ID_CATEG");
        $arrStructureCat = $hlbl_structure->selectData($select_hlbl);
        $json_arr = [];
        while($data = $arrStructureCat->Fetch()){
            if ($data[UF_CATEGORY] != '') {
                $this->filters_name[] = $data[UF_CATEGORY];
                $this->filters_sort[$data[UF_CATEGORY]] = $data[UF_SORT];
                $this->filter_section[$data[UF_CATEGORY]] = $data[UF_PRINT_NAME];
            }
        }

        //selet data from iblock
        $select_element = CIBlockElement::GetList(
            array("SORT" => "ASC"),
            array("IBLOCK_ID" => "22", "ACTIVE" => "Y"),
            false,
            false,
            array("NAME", "ACTIVE", "PROPERTY_ID_COLLECTION", "CODE", "ID", "PROPERTY_ROOT_SECTION", "PROPERTY_SHOW_IN_FILTER")
        );
        while($fields = $select_element->Fetch()) {
            $this->collection_code[$fields[NAME]] = $fields[PROPERTY_ID_COLLECTION_VALUE] . 'c';
            switch($fields[PROPERTY_ROOT_SECTION_VALUE]) {
                case "ОДЕЖДА":
                    $id_root = "267";
                    break;
                case "АКСЕССУАРЫ":
                    $id_root = "234";
                    break;
                case "ОДЕЖДА\АКСЕССУАРЫ":
                    $id_root = 'uniform';
                    break;
            }
            //$id_root = (($fields[PROPERTY_ROOT_SECTION_VALUE] == "ОДЕЖДА") ? "267" : "234");
            if ($id_root == $sect_id && $fields[PROPERTY_SHOW_IN_FILTER_VALUE] == "Да") {
                $this->collection_code_name[$fields[PROPERTY_ID_COLLECTION_VALUE] . 'c'] = $fields[NAME];
            }
            if($id_root == 'uniform' && $sect_id != "862" && $fields[PROPERTY_SHOW_IN_FILTER_VALUE] == "Да") {
                $this->collection_code_name[$fields[PROPERTY_ID_COLLECTION_VALUE] . 'c'] = $fields[NAME];
            }
            if($sect_id == "862" && $fields[PROPERTY_SHOW_IN_FILTER_VALUE] == "Да") {
                $this->collection_code_name[$fields[PROPERTY_ID_COLLECTION_VALUE] . 'c'] = $fields[NAME];
            }
        }
    }

    private function selectData() {
        $this->selection_elements = CIBlockElement::GetList(
            Array("ID" => "ASC"),
            Array("IBLOCK_ID" => IBLOCK_CATALOG, "SECTION_ID" => $this->root_id, "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE" => "Y", "CATALOG_AVAILABLE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да" ), //"SECTION_CODE" => $section_name,
            false,
            false,
            Array("*")
        );
    }

    protected function dataFill() {
        while($selection_array_ = $this->selection_elements->GetNextElement()) {
            $selection_array_p = $selection_array_->GetProperties();
            $selection_array_f = $selection_array_->GetFields();
            $p_like = '';
            $filter = '';
            $child_section = '';

            $colors = '';
            $sizes = '';

            if ($selection_array_p[LIKE][VALUE] == '') {
                $p_like = 'N';
            }

            else {
                $p_like = $selection_array_p[LIKE][VALUE];
            }

            foreach($this->filters_name as $filter_) {
                if($selection_array_p[$filter_][VALUE][0] != '') {
                    /// this section determinate
                    $child_section = $this->filter_section[$filter_];
                    $k = 0;
                    foreach($selection_array_p[$filter_][VALUE_ENUM_ID] as $k => $v) {
                        $filter .= $v . ',';
                        if (!in_array($v, array_keys($this->menu_catalog[strval($this->filters_sort[$filter_]) . '.' . strval($selection_array_p[$filter_][ID]) . 'k']))) {
                            if(count($this->menu_catalog[strval($this->filters_sort[$filter_]) . '.' . strval($selection_array_p[$filter_][ID]) . 'k']) == 0) {
                                $this->menu_catalog[strval($this->filters_sort[$filter_]) . '.' . strval($selection_array_p[$filter_][ID]) . 'k'] = array( $v => $selection_array_p[$filter_][VALUE][$k]);
                            }
                            $this->menu_catalog[strval($this->filters_sort[$filter_]) . '.' . strval($selection_array_p[$filter_][ID]) . 'k'] += array( $v => $selection_array_p[$filter_][VALUE][$k]);
                        }
                        $k++;
                    }
                    $filter .= $selection_array_p[$filter_][ID] . 'k' . ',';
                }
            }

            $filter = substr($filter, 0, strlen($filter) - 1);
            $colors = $selection_array_p[COLORS_TP][VALUE];
            $sizes = $selection_array_p[SIZES_TP][VALUE];
            if ($selection_array_p[SALE][VALUE] != "") {
                $discount = $selection_array_p[SALE][VALUE];
            }
            else {
                $discount = "";
            }
            /* здесь нужно вставлять или ID, или сам код корневой секции */
            if ($this->root_section[$this->root_id] != "sale") {
                $url = '/catalog/' . $this->root_section[$this->root_id] . '/' . $child_section . '/' . $selection_array_f[CODE] . '/';
            }
            else {
                $url = '/catalog/' . $child_section . '/' . $selection_array_f[CODE] . '/';
            }

            $collec = "";
            if ($selection_array_p[Collection][VALUE][0] != "") {
                foreach ($selection_array_p[Collection][VALUE] as $k => $v) {
                    $collec .= $this->collection_code[$v] . ",";
                }
                $collec = substr($collec, 0, strlen($collec) - 1);
            }
            else {
                $collec = 'null';
            }

            $this->data_catalog[] = array(
                "id" => $selection_array_f[ID],
                "code" => $selection_array_f[CODE],
                "preview_pic" => CFile::GetPath($selection_array_p[Preview_photo][VALUE]),
                "detail_pic" => CFile::GetPath($selection_array_p[On_model_photo][VALUE]),
                "url" => $url,
                "article" => $selection_array_p[CML2_ARTICLE][VALUE],
                "like" => $p_like,
                "min_price" => $selection_array_p[MIN_PRICE][VALUE],//priceFormater(),
                "napisanie_tovara" => $selection_array_p[NAPISANIE_TOVARA][VALUE],
                "collection" => $collec,
                "filter" => $filter,
                "filter_sizes" => $sizes,
                "filter_colors" => $colors,
                "root_id" => $this->root_id,
                "discount" => $discount,
                "sort" => $selection_array_f[SORT],
                "sort_c1" => $selection_array_p[SORT_ALT][VALUE],
                "sort_c2" => $selection_array_p[SORT_ALT2][VALUE],
                "sort_c3" => $selection_array_p[SORT_ALT3][VALUE],
                "sort_c4" => $selection_array_p[SORT_ALT4][VALUE]
            );
        }

        $select_hlbl = array("UF_NAME", "UF_XML_ID", "UF_SORT");
        $entity_color = new WorkerHLBlock(HLBL_COLOR);
        $colorsArr = $entity_color->selectData($select_hlbl);
        $arrColors = array();

        while($data = $colorsArr->Fetch()){
            if ($data[UF_SORT] < 100) {
                $arrColors[$data[UF_XML_ID]] = $data[UF_NAME];
            }
        }

        $this->menu_catalog["97.183k"] = $this->collection_code_name;

        $this->menu_catalog["98.130k"] = $arrColors;

        $this->menu_catalog["99.129k"] = array(
            "22" => "One Size",
            "17" => "XS",
            "18" => "S",
            "19" => "M",
            "20" => "L",
            "21" => "XL"
        );
    }

    public function insertDataHighload() {
        $this->dataFill();

        $hlbl_catalog = new WorkerHLBlock(HLBL_CATALOG);
        $hlbl_menu = new WorkerHLBlock(HLBL_MENU);

        foreach($this->data_catalog as $key => $val) {
            $like = 0;
            if($val[like] == "N") {
                $like = 0;
            }
            else {
                $like = 1;
            }
            $numer_price = explode(' ', $val[min_price]);
            if ($numer_price[1] != "₽") {
                $res_price = floatval($numer_price[0] . $numer_price[1]);
            }
            else {
                $res_price = floatval($numer_price[0]);
            }
            $discount_price = round($res_price - $res_price * ($val[discount] / 100.0), 0);

            $data_insert = array(
                "UF_ID" => intval($val[id]),
                "UF_CODE" => strval($val[code]),
                "UF_PREVIEW_PIC" => strval($val[preview_pic]),
                "UF_DETAIL_PIC" => strval($val[detail_pic]),
                "UF_URL" => strval($val[url]),
                "UF_ARTICLE" => strval($val[article]),
                "UF_LIKE" => $like,
                "UF_MIN_PRICE" => strval($val[min_price]),
                "UF_NAPISANIE_TOVARA" => strval($val[napisanie_tovara]),
                "UF_COLLECTION" => strval($val[collection]),
                "UF_FILTER" => strval($val[filter]),
                "UF_FILTER_SIZES" => strval($val[filter_sizes]),
                "UF_FILTER_COLORS" => strval($val[filter_colors]),
                "UF_ROOT_ID" => intval($val[root_id]),
                "UF_PRICE_SORT" => $res_price,
                "UF_SALE" => intval($val[discount]),
                "UF_BX_SORT" => intval($val[sort]),
                "UF_SORT_C1" => intval($val[sort_c1]),
                "UF_SORT_C2" => intval($val[sort_c2]),
                "UF_SORT_C3" => intval($val[sort_c3]),
                "UF_SORT_C4" => intval($val[sort_c4]),
                "UF_PRICE_SALE_SORT" => $discount_price
            );

            $hlbl_catalog->pushNewData($data_insert);
        }

        foreach($this->menu_catalog as $key => $val) {
            foreach($val as $k2 => $v2) {
                $data_insert = array(
                    "UF_CANON_ID" => intval($this->root_id),
                    "UF_ROOT_ID" => strval($key),
                    "UF_CATEG_ID" => strval($k2),
                    "UF_NAME_CAT" => strval($v2)
                );
                $hlbl_menu->pushNewData($data_insert);
            }
        }
    }
}

$dataClothes = new MenuDataCatGenerate("267");
$dataClothes->insertDataHighload();

$dataAccessories = new MenuDataCatGenerate("234");
$dataAccessories->insertDataHighload();

$dataAccessories = new MenuDataCatGenerate("862");
$dataAccessories->insertDataHighload();

?>
