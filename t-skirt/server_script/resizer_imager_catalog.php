<?php

ini_set('error_reporting', E_ERROR);
ini_set('display_errors', 1);
$_SERVER["DOCUMENT_ROOT"] = "/home/t/tskirt/public_html";
require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

define("IBLOCK_CATALOG", "8");
define("IBLOCK_OFFERS", "9");

CModule::IncludeModule('iblock');

class MicroGen
{
    public $pic;
    private $size_p;
    public $need_update;
    //private $pr;
    public $file_resize;
    public $path_to_store;

    public function __construct() {
        $this->size_p = array( "HEIGHT" => "", "WIDTH" => "", "FILE_NAME" => "");
        $this->path_to_store = "/upload/resized_pic_catalog/";
        $this->need_update = false;
    }

    protected function getResize($id_pic) {
        $full_micro_t = strval(microtime());
        $arrMicro = explode(' ', $full_micro_t);
        $unic_name_pic = md5($arrMicro[1] + ($arrMicro[0] + (rand(1000, 9999) / 10000))) . ".jpg";
        $this->pic = $unic_name_pic;

        $file_pic_orig = CFile::GetByID($id_pic);
        $pic_fields = $file_pic_orig->Fetch();
        $this->size_p[HEIGHT] = $pic_fields[HEIGHT];
        $this->size_p[WIDTH] = $pic_fields[WIDTH];
    }

    public function genResizeImage($id_pic) {
        $this->getResize($id_pic);

        $proportion = $this->size_p[WIDTH] / $this->size_p[HEIGHT];
        $w = 325;
        $h = intval(325 / $proportion);

        $this->file_resize = ($_SERVER["DOCUMENT_ROOT"] . $this->path_to_store . $this->pic);

        //checkUpToDate($prop_need_ref, $min_pic);

        if($this->need_update) {
            usleep(50000);
            $file_resize = CFile::ResizeImageFile(
                $p = ($_SERVER["DOCUMENT_ROOT"] . CFile::GetPath($id_pic)),
                $pd = ($_SERVER["DOCUMENT_ROOT"] . $this->path_to_store . $this->pic),
                $s = array('width'=>$w,'height'=>$h),
                $m = BX_RESIZE_IMAGE_EXACT,
                $wm = array(),
                $qu = "92",
                $af = false
            );
        }
    }

    public function checkUpToDate($prop_need_ref, $min_pic) {
        if (!$min_pic) {
            $this->need_update = true;
        }
        else {
            if ($prop_need_ref == "Нет") {
                $this->need_update = false;
            }
            else {
                $this->need_update = true;
            }

        }

    }
}

# name of fields for info image
## HEIGHT; WIDTH; FILE_NAME
// "267", "234"

$selection_iblock_elements = CIBlockElement::GetList(
    Array("ID" => "ASC"),
    Array("IBLOCK_ID" => IBLOCK_CATALOG, "SECTION_ID" => array("267","234"), "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE" => "Y", "CATALOG_AVAILABLE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да" ), //"SECTION_CODE" => $section_name,
    false,
    false,//array("nTopCount" => "100"),//
    Array("*")
);

$file_processor = new MicroGen();

while($fields_element = $selection_iblock_elements->GetNextElement()) {
    $fields_element_f = $fields_element->GetFields();
    $fields_element_p = $fields_element->GetProperties();

    $file_processor->genResizeImage(
        $fields_element_f[PREVIEW_PICTURE],
        $fields_element_p[Refresh_catalog_pic][VALUE],
        $fields_element_p[On_model_photo][VALUE]
    );

    $file_processor->checkUpToDate($fields_element_p[Refresh_catalog_pic][VALUE], $fields_element_p[Preview_photo][VALUE]);
    if($file_processor->need_update) {
        $file_processor->genResizeImage($fields_element_f[PREVIEW_PICTURE]);
        # preview
        CIBlockElement::SetPropertyValues(
            $fields_element_f['ID'],
            IBLOCK_CATALOG,
            array(
                "253" => CFile::MakeFileArray($file_processor->file_resize)
            ),
            "253"
        );
        CIBlockElement::SetPropertyValues(
             $fields_element_f['ID'],
            IBLOCK_CATALOG,
            array(
                "252" => 143
            ),
            "252"
        );
    }
    $file_processor->checkUpToDate($fields_element_p[Refresh_catalog_pic][VALUE], $fields_element_p[On_model_photo][VALUE]);
    if($file_processor->need_update) {
        if($fields_element_f[DETAIL_PICTURE] != "") {
            $file_processor->genResizeImage($fields_element_f[DETAIL_PICTURE]);
            # on model
            CIBlockElement::SetPropertyValues(
                $fields_element_f['ID'],
                IBLOCK_CATALOG,
                array(
                    "254" => CFile::MakeFileArray($file_processor->file_resize)
                ),
                "254"
            );
            CIBlockElement::SetPropertyValues(
                $fields_element_f['ID'],
                IBLOCK_CATALOG,
                array(
                    "252" => 143
                ),
                "252"
            );
        }
    }
}

?>