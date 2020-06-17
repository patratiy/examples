<?php
ini_set('display_errors', '1');
ini_set('error_reporting', 'E_ALL');

$_SERVER["DOCUMENT_ROOT"] = "/home/c/cp31573/public_html";
require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_before.php');

CModule::IncludeModule("iblock");

class ImageInspector
{
    private $ids_elem_select;
    private $array_image_add;
    protected $work_folder;
    private $select_element;

    public function __construct($path_work_folder, $path) {

        $this->work_folder = $path_work_folder;
        $handle = fopen($path, "r");
        while (($line = fgets($handle)) !== false) {
            $parts = explode(';', $line);
            $images = [];
            $i_count = 0;
            foreach($parts as $part) {
                if ($i_count > 1) {
                    $images[] = $this->work_folder . "/" . $parts[0] . "/" . str_replace("\n", "", $part) . ".jpg";
                }
                $i_count++;
            }
            $this->array_image_add[$parts[1]] = array('count' => count($parts), 'id' => $parts[1], 'old_id' => $parts[0], 'imgs' => $images);
            $this->ids_elem_select[] = $parts[1];
        }
        fclose($handle);

        $this->select_element = CIBlockElement::GetList(
            array("SORT" => "ASC"),
            array("IBLOCK_ID" => "5", "ID" => $this->ids_elem_select),
            false,
            false,//array('nTopCount'=>"2"),
            array("ID", "SORT", "NAME", "IBLOCK_ID", "CODE", "PROPERTY_OBJECT_PICTURES")
        );
    }
    #download images method
    public function downloadImgUpdate() {
        foreach($array_image_add as $elem) {
            //echo $elem['id'];
            $path = $this->work_folder . "/" . $elem["old_id"] . "/";
            if (file_exists($path)) {
                chdir($path);
            }
            else {
                mkdir($path);
                chdir($path);
            }

            foreach($elem['imgs'] as $img) {
                if (!file_exists($img)) {
                    $leng_path = count(explode("/", $img));
                    shell_exec('wget https://images.jqestate.ru/' . str_replace(".jpg", "", explode("/", $img)[$leng_path - 1]));
                    rename($path . "/" . str_replace(".jpg", "", explode("/", $img)[$leng_path - 1]), $path . "/" . explode("/", $img)[$leng_path - 1]);
                }
            }
        }
    }

    public function execInstect() {
        #run across all elements, witch will be updated
        while($elements = $this->select_element->Fetch()) {
            $elem_iblock = $this->array_image_add[$elements[ID]];

            $names_curr_stack = array();
            foreach($elements['PROPERTY_OBJECT_PICTURES_VALUE'] as $item) {
                $elem_obj = CFile::GetByID($item);
                $names_curr_stack[] = str_replace('.jpg', '', $elem_obj->Fetch()['ORIGINAL_NAME']);
            }

            foreach($elem_iblock['imgs'] as $img) {
                $leng_path = count(explode("/", $img));
                # check if image not yet in property of infoblock element
                if(!in_array(str_replace(".jpg", "", explode("/", $img)[$leng_path - 1]), $names_curr_stack)) {
                    
                    $arFile = CFile::MakeFileArray($img);
                    $arFile["MODULE_ID"] = "iblock";
                    CIBlockElement::SetPropertyValueCode($elem_iblock['id'], "OBJECT_PICTURES", Array("VALUE"=>$arFile));
                }
            }
        }
    }

    #delete element from property multiple file type
    public function deletePropElement($id_prop_value_id, $id_elem) {
        $arFile["MODULE_ID"] = "iblock";
        $arFile["del"] = "Y";
        # this expression for delete element of multiple properties from table
        CIBlockElement::SetPropertyValueCode($id_elem, "OBJECT_PICTURES", Array ($id_prop_value_id => Array("VALUE"=>$arFile)));
    }
}
//this point to file with list of need images.
$image_download = new ImageInspector($_SERVER["DOCUMENT_ROOT"] . "/upload/tmp", $_SERVER['DOCUMENT_ROOT'] . '/upload/img_need_to_download.csv');
$image_download->execInstect();

echo "ready";

?>