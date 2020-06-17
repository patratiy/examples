<?

##
# ImaginWeb 2018 Copyright - this script use for generate left menu struture use IBLOCK 8 and 22
#

ini_set('error_reporting', E_ERROR);
ini_set('display_errors', 1);
$_SERVER["DOCUMENT_ROOT"] = "/home/t/tskirt/public_html";

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/local/lib/highloadbk.php");

define("IBLOCK_CATALOG", "8");
define("HLBL_STRUCTURE", "7");

class workerPropEnum
{
    private $replacer;
    public $latin;
    public $id_created_enum;
    public $selected_enums;

    public function __construct() {
        $this->selected_enums = [];
        $this->replacer = [
            "А"=>"A","а"=>"a",
            "Б"=>"B","б"=>"b",
            "В"=>"V","в"=>"v",
            "Г"=>"G","г"=>"g",
            "Д"=>"D","д"=>"d",
            "Е"=>"Ye","е"=>"e",
            "Ё"=>"Ye","ё"=>"e",
            "Ж"=>"Zh","ж"=>"zh",
            "З"=>"Z","з"=>"z",
            "И"=>"I","и"=>"i",
            "Й"=>"Y","й"=>"y",
            "К"=>"K","к"=>"k",
            "Л"=>"L","л"=>"l",
            "М"=>"M","м"=>"m",
            "Н"=>"N","н"=>"n",
            "О"=>"O","о"=>"o",
            "П"=>"P","п"=>"p",
            "Р"=>"R","р"=>"r",
            "С"=>"S","с"=>"s",
            "Т"=>"T","т"=>"t",
            "У"=>"U","у"=>"u",
            "Ф"=>"F","ф"=>"f",
            "Х"=>"Kh","х"=>"kh",
            "Ц"=>"Ts","ц"=>"ts",
            "Ч"=>"Ch","ч"=>"ch",
            "Ш"=>"Sh","ш"=>"sh",
            "Щ"=>"Shch","щ"=>"shch",
            "Ъ"=>"","ъ"=>"",
            "Ы"=>"Y","ы"=>"y",
            "Ь"=>"","ь"=>"",
            "Э"=>"E","э"=>"e",
            "Ю"=>"Yu","ю"=>"yu",
            "Я"=>"Ya","я"=>"ya",
            " "=>"_","a"=>"a",
            "b"=>"b","c"=>"c",
            "d"=>"d","e"=>"e",
            "f"=>"f",
            "g"=>"g","h"=>"h",
            "i"=>"i","j"=>"j",
            "k"=>"k","l"=>"l",
            "m"=>"m","n"=>"n",
            "o"=>"o","p"=>"p",
            "q"=>"q","r"=>"r",
            "s"=>"s","t"=>"t",
            "u"=>"u","v"=>"v",
            "w"=>"w","x"=>"x",
            "y"=>"y","z"=>"z",
            "A"=>"a",
            "B"=>"b","C"=>"c",
            "D"=>"d","E"=>"e",
            "F"=>"f",
            "G"=>"g","H"=>"h",
            "I"=>"i","J"=>"j",
            "K"=>"k","L"=>"l",
            "M"=>"m","N"=>"n",
            "O"=>"o","P"=>"p",
            "Q"=>"q","R"=>"r",
            "S"=>"s","T"=>"t",
            "U"=>"u","V"=>"v",
            "W"=>"w","X"=>"x",
            "Y"=>"y","Z"=>"z"
        ];
    }

    public function removeEnum($id) {
        CIBlockPropertyEnum::Delete($id);
    }
    /*
    $id_element - this is mean id element of list
    */
    public function updateEnum($id_element, $arr_f) {
        $prop_enum = new CIBlockPropertyEnum;
        $prop_enum->Update(
            $id_element,
            $arr_f //this num of update
        );
    }

    public function addEnumCollection($name, $id_prop) {
        $prop_enum = new CIBlockPropertyEnum;
        $this->convertToLatin($name);
        $this->id_created_enum = $prop_enum->Add(
            Array(
                'PROPERTY_ID'=>$id_prop,
                'VALUE'=>$name,
                'XML_ID' => str_replace("_", "", $this->latin)
            )
        );
    }

    public function selectAllEnum($id_prop) {
        $prop_values = CIBlockProperty::GetPropertyEnum(
            $id_prop,
            Array("SORT"=>"ASC"),
            Array()
        );

        while($per_value = $prop_values->Fetch()) {
            $this->selected_enums[$per_value['ID']] = [
                'id' => $per_value['ID'],
                'name' => $per_value['VALUE'],
                'xml_id' => $per_value['XML_ID']
            ];
        }
    }

    public function convertToLatin($word) {
        $result = '';
        foreach(str_split(iconv('utf-8', 'cp1251', $word)) as $key => $val) {
            $result .= $this->replacer[iconv('cp1251', 'utf-8', $val)];
        }
        $this->latin = $result;
    }
}


$hlbl_catalog = new WorkerHLBlock(HLBL_STRUCTURE);

CModule::IncludeModule("iblock");
CModule::IncludeModule('catalog');

if ($argv[1] = 'update_hlbl_struct' || isset($_GET['get_hlbl'])) {
    $hlbl_catalog->cleanUp();
}

$ib_list_1c_catalog = CIBlockSection::GetList(
    Array("SORT" => "ASC"),
    Array(
        "IBLOCK_ID" => IBLOCK_CATALOG,
        "ACTIVE" => "Y",
        "UF_USE_IN_STRUCT" => "1",
        "DEPTH_LEVEL" => Array("1", "2") // выделяем только верхний и его дочерний уровень
    ),
    true,
    Array('ID','NAME','SECTION_PAGE_URL','IBLOCK_SECTION_ID', 'SORT', 'ACTIVE', 'UF_ACTIVE', 'CODE', "UF_CATEGORY"),
    false
);

$result = CIBlockProperty::GetList(
    array("id" => "asc"),
    array("IBLOCK_ID" => "8", "PROPERTY_TYPE" => "L")
);

$properties_ib8_set = [];
while($selected_prop_ib8 = $result->Fetch()) {
    if ($selected_prop_ib8["NAME"] == "Коллекция") {
        $field_selected[ID] = $selected_prop_ib8["ID"];
    }
    $properties_ib8_set[$selected_prop_ib8[CODE]] = $selected_prop_ib8[ID];
}

//echo "\n" . $field_selected[ID];


if ($argv[1] = 'update_hlbl_struct' || isset($_GET['get_hlbl'])) {
    while($val = $ib_list_1c_catalog->Fetch()) {
        $data_insert = array(
            "UF_ROOT_SEC" => intval($val['IBLOCK_SECTION_ID']),
            "UF_SEC_CODE" => intval($val['ID']),
            "UF_SORT" => intval($val['SORT']),
            "UF_SEC_NAME" => strval($val['NAME']),
            "UF_CATEGORY" => strval($val['UF_CATEGORY']),
            "UF_PRINT_NAME" => strval($val['CODE']),
            "UF_ID_CATEG" => strval($properties_ib8_set[$val['UF_CATEGORY']] . 'k')
        );
        $hlbl_catalog->pushNewData($data_insert);
    }
}

$prop_proc = new workerPropEnum();

$select_element = CIBlockElement::GetList(
    array("SORT" => "ASC"),
    array("IBLOCK_ID" => "22"),
    false,
    false,
    array("NAME", "ACTIVE", "PROPERTY_ID_COLLECTION", "CODE", "ID", "PROPERTY_ROOT_SECTION")
);

$prop_proc->selectAllEnum($field_selected[ID]);
$keys_enum = array_keys($prop_proc->selected_enums);

$count_active_elem = 0;
$current_id_collection_ib = [];
$select_data_ib = [];


while($fields = $select_element->Fetch()) {

    $id_elem = $fields[ID];
    $name = $fields[NAME];
    $act = $fields[ACTIVE];
    $code = $fields[CODE];
    $id = $fields[PROPERTY_ID_COLLECTION_VALUE];
    //print_r($fields);
    switch($fields[PROPERTY_ROOT_SECTION_VALUE]) {
        case "ОДЕЖДА":
            $root_section = "267";
            break;
        case "АКСЕССУАРЫ":
            $root_section = "234";
            break;
        case "ОДЕЖДА\АКСЕССУАРЫ":
            $root_section = "uniform";
            break;
    }

    if($act == "N") {
        $prop_proc->removeEnum($id);
    }
    else {
        $count_active_elem++;
        $current_id_collection_ib[] = $id;

        if ($argv[1] = 'update_hlbl_struct' || isset($_GET['get_hlbl'])) {
            if ($root_section == "uniform") {
                foreach(array("267", "234") as $id_) {
                    $data_insert = array(
                        "UF_ROOT_SEC" => intval($id_),
                        "UF_SEC_NAME" => strval($name),
                        "UF_PRINT_NAME" => strval($code),
                        "UF_ID_CATEG" => strval($id . 'c')
                    );
                    $hlbl_catalog->pushNewData($data_insert);
                }
            }
            else {
                $data_insert = array(
                    "UF_ROOT_SEC" => intval($root_section),
                    "UF_SEC_NAME" => strval($name),
                    "UF_PRINT_NAME" => strval($code),
                    "UF_ID_CATEG" => strval($id . 'c')
                );
                $hlbl_catalog->pushNewData($data_insert);
            }
        }
    }
    if($name != $prop_proc->selected_enums[$id]['name'] && $act == "Y") {
        $prop_proc->convertToLatin($name);
        $arr_upd = array("VALUE" => $name, "XML_ID" => str_replace("_", "", $prop_proc->latin));
        $prop_proc->updateEnum($id, $arr_upd);
        $elem = new CIBlockElement;
        $res = $elem->Update(
            $id_elem,
            array("CODE" => strtolower($prop_proc->latin))
        );
    }
    if(!in_array($id, $keys_enum) && $act == "Y") {
        $prop_proc->addEnumCollection($name, $field_selected[ID]);
        CIBlockElement::SetPropertyValueCode($id_elem, "ID_COLLECTION", $prop_proc->id_created_enum);
        $elem = new CIBlockElement;
        $prop_proc->convertToLatin($name);
        $res = $elem->Update(
            $id_elem,
            array("CODE" => strtolower($prop_proc->latin))
        );
    }
}

// check if some element from IBLOCK was deleted - remove element of list for Collection Prop - len not equal
$prop_proc->selectAllEnum($field_selected[ID]);

if(count($keys_enum) != $count_active_elem) {
    foreach($keys_enum as $key_) {
        if(!in_array($key_, $current_id_collection_ib)) {
            $prop_proc->removeEnum($key_);
        }
    }
}

    echo "{";
    header("Content-Type: application/json");
    echo '"struct":' . json_encode(array('ready' => '1'));
    echo "}";

?>