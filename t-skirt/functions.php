<?php

##Collection work class for manage site ImaginWeb 2018 Copyright
#27.07.2018 15:25:30

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('catalog');
CModule::IncludeModule('iblock');

class Price
{
    private $val;

    public function __construct() {
        $this->val = 0;
    }

    public function priceFormater($price) {
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
}

class ImageResizer
{
    public $src;
    private $width;

    public function __construct($width) {
        $this->width = $width;
    }

    public function getByWidth($img_obj) {
        $pic_ = CFile::GetById($img_obj[ID]);
        $pic = $pic_->Fetch();
        $ratio_each = $pic[WIDTH] / $pic[HEIGHT];

        $renderImage = CFile::ResizeImageGet($img_obj, Array("width" => $this->width, "height" => $this->width / $ratio_each));
        $this->src = $renderImage['src'];
    }
}

class FileWorker
{
    public $unic_name;
    public $new_path;
    protected $width;
    public $array_obj;
    private $ib_id;
    private $img_quality;
    //private $unseted;

    public $prop_image_more;

    public function __construct() {
        $this->width = 600;
        $this->ib_id = 8;
        $this->array_obj = array();
        $this->prop_image_more = 103;
        $this->img_quality = 95;
        //$this->unseted = false;
    }

    public function getUnicName($ext) {
        $full_micro_t = strval(microtime());
        $arrMicro = explode(' ', $full_micro_t);
        $unic_name_pic = md5($arrMicro[1] + ($arrMicro[0] + (rand(1000, 9999) / 10000))) . "." . $ext;
        $this->unic_name = $unic_name_pic;
    }

    public function imageTempCreate($id_file, $id_elem) {
        $this->getUnicName('jpg');

        $rFile = CFile::GetByID($id_file);
        $data = $rFile->Fetch();

        $file_path_name = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $data['SUBDIR'] . '/' . $data['FILE_NAME'];
        $this->new_path = $_SERVER['DOCUMENT_ROOT'] . '/upload/resized_pic_catalog/' . $this->unic_name;
        if ($data[WIDTH] > 600) {
            $this->backupOriginal($data, $id_elem);
            $this->array_obj[$id_file] = $this->new_path;

            $ratio = $data[WIDTH] / $data[HEIGHT];

            CFile::ResizeImageFile(
                $file_path_name,
                $this->new_path,
                array("width" => $this->width, "height" => $this->width / $ratio),
                BX_RESIZE_IMAGE_EXACT,
                array(),
                $this->img_quality,
                false
            );
        }
    }

    protected function backupOriginal($fileArr, $id_elem) {
        if (!file_exists($_SERVER[DOCUMENT_ROOT] . "/upload/originals/$id_elem/" . str_ireplace(' ', '_', $fileArr[FILE_NAME]))) {
            CFile::CopyFile($fileArr[ID], true, "/originals/$id_elem/" . str_ireplace(' ', '_', $fileArr[FILE_NAME]));
        }
    }

    public function fileDelete($path) {
        //$resizedFile = CFile::MakeFileArray($path);
        unlink($path);
    }

    public function unsetProp($id_elem) {
        if (!$this->unseted) {
            CIBlockElement::SetPropertyValuesEx($id_elem, $this->ib_id, array($this->prop_image_more => Array ("VALUE" => array("del" => "Y"))));
            $this->unseted = true;
        }
    }

    public function fileIBUpdate($id_elem, $id_file, $type) {
        $elem_photos = new CIBlockElement;
        switch($type) {
            case 'prev':
                $updatePhoto = array("PREVIEW_PICTURE" => CFile::MakeFileArray($this->array_obj[$id_file]));
                break;
            case 'detail':
                $updatePhoto = array("DETAIL_PICTURE" => CFile::MakeFileArray($this->array_obj[$id_file]));
                break;
        }
        usleep(15000);
        $res = $elem_photos->Update($id_elem, $updatePhoto);
        //echo "<b>" . $res . "</b>";
    }

    public function fileIBProp($id_elem, $id_file, $type) {
        $fArr = CFile::MakeFileArray($this->array_obj[$id_file]);
        usleep(15000);
        CIBlockElement::SetPropertyValueCode($id_elem, $this->prop_image_more, array("VALUE" => $fArr));
        //usleep(10000);
    }

    public function batchProcessor($id_elem, $array_file) {
        foreach($array_file as $file_id => $type) {
            $this->imageTempCreate($file_id, $id_elem);
        }


        $keys_updates = array_keys($this->array_obj);

        foreach($array_file as $file_id => $type) {
            if (in_array($file_id, $keys_updates)) {
                switch($type) {
                    case 'prev':
                        $this->fileIBUpdate($id_elem, $file_id, $type);
                        $this->fileDelete($this->array_obj[$file_id]);
                        break;
                    case 'detail':
                        $this->fileIBUpdate($id_elem, $file_id, $type);
                        $this->fileDelete($this->array_obj[$file_id]);
                        break;
                    case 'more_photo':
                        $this->unsetProp($id_elem);
                        $this->fileIBProp($id_elem, $file_id, $type);
                        $this->fileDelete($this->array_obj[$file_id]);
                        break;
                }
            }
        }

        //clean tmp file
        unset($this->array_obj);
        $this->unseted = false;
    }
}

class ImageResizerCS extends ImageResizer
{
    public function __construct($img_obj, $wid) {
        $pic_ = CFile::GetById($img_obj);
        $pic = $pic_->Fetch();
        $ratio_each = $pic[WIDTH] / $pic[HEIGHT];

        $renderImage = CFile::ResizeImageGet($img_obj, Array("width" =>$wid, "height" => $wid / $ratio_each));
        $this->src = $renderImage['src'];
    }
}

class LikeBlock
{
    protected $rsLIKE;
    protected $rsPRICE;
    protected $iblock;
    public $name_section;

    public function __construct($iblock) {
        $this->iblock = $iblock;
        // select from iblock
        $this->rsLIKE = CIBlockElement::GetList (
            Array("SORT"=>"ASC"),
            Array("IBLOCK_ID" => $iblock, "ACTIVE" => "Y", "PROPERTY_LIKE_VALUE" => "Y", "CATALOG_AVAILABLE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да"),
            false,
            Array("nTopCount" => 5),
            Array("IBLOCK_ID", "ID", "NAME", "PREVIEW_TEXT" , "PREVIEW_PICTURE", "DETAIL_PAGE_URL", "PROPERTY_LIKE", "PROPERTY_NAPISANIE_TOVARA", "PROPERTY_KOMMENTARIY_K_NAPISANIYU", "PROPERTY_F_USER", "PROPERTY_ON_MODEL_PHOTO", "PROPERTY_PREVIEW_PHOTO", "PROPERTY_SALE")
        );
    }

    protected function priceOfferGet($prod_id, $sku_iblock) {
        $mxResult = CCatalogSKU::GetInfoByProductIBlock($this->iblock);
        $this->rsPRICE = CIBlockElement::GetList (
              Array("SORT"=>"ASC"),
              Array("IBLOCK_ID" => $sku_iblock, 'PROPERTY_' . $mxResult['SKU_PROPERTY_ID'] => $prod_id, "ACTIVE" => "Y"),//$LikeProduct[ID]
              false,
              Array('nTopCount' => 1),
              Array("IBLOCK_ID", "ID", "NAME", "PREVIEW_TEXT" , "PREVIEW_PICTURE", "DETAIL_PAGE_URL", "PROPERTY_CML2_LINK")
        );
    }

    public function printLikeForm($sku_iblock, $user_id) {
        print
        '<section id="like">'.
            '<div class="container">' .
                '<div class="category-collapse" type="button" data-toggle="collapse" data-target="#like-collapse" aria-expanded="true" aria-controls="like-collapse">' .
                    '<div class="catalog-name">' .
                        '<hr />' .
                        '<div class="catalog-name-wrap">' .
                            '<h1>' . $this->name_section . '</h1>' .
                        '</div>' .
                    '</div>' .
                    '<img src="/bitrix/templates/addeo/img/arrow-collapse.png" alt="">' .
                '</div>' .
                '<div class="collapse in" id="like-collapse">' .
                    '<div class="collapse-wrap">';

        while($LikeProduct = $this->rsLIKE->GetNextElement()) {
					if($_SERVER["REMOTE_ADDR"] == "46.148.203.30"){
							/*?><pre><?
							print_r($LikeProduct);
							?></pre><?*/
						}
            print
                        '<div class="like-block">';

            $product_prop = $LikeProduct->GetProperty('F_USER');
            $LikeProduct = $LikeProduct->fields;

            $this->priceOfferGet($LikeProduct[ID], $sku_iblock);

            //$i="0";
            //while($arOffer = $rsPRICE-> GetNext()) {

            $arOffer = $this->rsPRICE->Fetch();

            $price_all = GetCatalogProductPrice($arOffer["ID"], 1);
            $price = FormatCurrency($price_all["PRICE"], $price_all["CURRENCY"]);

            //$price_alls[$i] = $price_all[$i]["PRICE"];
            //$i++;
            //}

            $price_form = new Price();

            $pr_base = $price_form->priceFormater(round(intval(str_ireplace(' ', '', $price)) / ((100.0 - $LikeProduct["PROPERTY_SALE_VALUE"]) / 100.0), 0));

            print
                            '<a href="' . $LikeProduct["DETAIL_PAGE_URL"] . '" onclick="javascript:;">' .
                                '<div class="catalog-bg" style="background-image: url(' . CFile::GetPath($LikeProduct["PROPERTY_PREVIEW_PHOTO_VALUE"]) . ');"></div>' .
                                '<div class="catalog-bg-2" style="background-image: url(' . (($LikeProduct["PREPERTY_ON_MODEL_PHOTO_VALUE"]) ? CFile::GetPath($LikeProduct["PREPERTY_ON_MODEL_PHOTO_VALUE"]) : CFile::GetPath($LikeProduct["PROPERTY_PREVIEW_PHOTO_VALUE"])) . ');"></div>' .
                                '<div class="bottom-name">' .
                                    '<h2>' . $LikeProduct["PROPERTY_NAPISANIE_TOVARA_VALUE"] . '</h2>' .
                                    '<p>' . $LikeProduct["PROPERTY_KOMMENTARIY_K_NAPISANIYU_VALUE"] . '</p>' .
                                    '<h3>' . (($LikeProduct["PROPERTY_SALE_VALUE"] != 0) ? '<span class="discount-price">' . $price. '</span>&nbsp;<span class="striked-price">' . $pr_base . '</span>' : $price) . '</h3>' .
                                '</div>' .
                            '</a>' .
                            '<div class="bottom-hover">' .
                                '<div class="bottom-hover-wish ' . (in_array($user_id, $product_prop['VALUE']) ? 'active' : '') . '" data-id="' . $LikeProduct["ID"] . '">' .
                                    '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">' .
                                    '<g>' .
                                        '<g>' .
                                            '<path fill="#393939" d="M369.023,20.829c-45.596,0-86.692,21.459-113.023,55.438c-26.327-33.974-67.422-55.438-113.024-55.438
                                            C64.139,20.829,0,84.968,0,163.806c0,91.606,58.371,172.546,125.009,234.287c60.616,56.162,120.395,89.59,122.911,90.986
                                            c2.512,1.395,5.297,2.092,8.08,2.092s5.568-0.697,8.081-2.092c2.515-1.396,62.294-34.824,122.911-90.986
                                            C453.627,336.354,512,255.415,512,163.806C512,84.968,447.861,20.829,369.023,20.829z M364.859,373.188
                                            c-45.079,41.865-90.758,71.084-108.852,82.012C215.128,430.462,33.312,312.051,33.312,163.806
                                            c0-60.469,49.196-109.664,109.665-109.664c41.8,0,79.401,23.261,98.127,60.709c2.821,5.643,8.588,9.206,14.897,9.206
                                            s12.075-3.564,14.897-9.207c18.724-37.447,56.324-60.708,98.126-60.708c60.469,0,109.665,49.196,109.665,109.665
                                            C478.688,249.491,416.788,324.963,364.859,373.188z"/>' .
                                        '</g>' .
                                    '</g>' .
                                    '</svg>' .
                                    '<p>Добавить<br>в избранное</p>' .
                                '</div>' .
                                '<div class="bottom-hover-quick" data-id="' . $LikeProduct["ID"] . '">' .
                                    '<svg version="1.1" id="Слой_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 262.4 270.8" style="enable-background:new 0 0 262.4 270.8;" xml:space="preserve">' .
                                        '<path fill="#393939" d="M107.4,0C48.1,0,0,48.1,0,107.4s48.1,107.4,107.4,107.4s107.4-48.1,107.4-107.4S166.7,0,107.4,0z M107.4,193.6
                                        c-47.7,0-86.3-38.7-86.3-86.3c0-47.7,38.7-86.3,86.3-86.3s86.3,38.7,86.3,86.3C193.7,155,155,193.6,107.4,193.6z M259.9,254.9
                                        L173,168c-0.2,0.2-0.3,0.3-0.5,0.5c1.7,1.9,2.3,5,0,6.9c-3.5,2.9-7.1,5.8-10.9,8.2l84.7,84.7c3.3,3.3,8.7,3.3,12,0l1.5-1.5
                                        C263.2,263.6,263.2,258.2,259.9,254.9z M174.1,97.8h-13.7H51.3H40.7c-4.7,0-8.5,3.8-8.5,8.5v2.1c0,4.7,3.8,8.5,8.5,8.5h13.7h109.1
                                        h10.6c4.7,0,8.5-3.8,8.5-8.5v-2.1C182.6,101.6,178.8,97.8,174.1,97.8z M97.8,40.6v13.7v109.1V174c0,4.7,3.8,8.5,8.5,8.5h2.1
                                        c4.7,0,8.5-3.8,8.5-8.5v-13.7V51.2V40.6c0-4.7-3.8-8.5-8.5-8.5h-2.1C101.6,32.1,97.8,35.9,97.8,40.6z"></path>' .
                                    '</svg>' .
                                    '<p>Быстрый<br>просмотр</p>' .
                                '</div>' .
                            '</div>' .
                        '</div>';
        }
        print
                    '</div>' .
                '</div>' .
            '</div>' .
        '</section>';
    }
}

class MoreProdBlock extends LikeBlock
{

    public function __construct($iblock, $see_also_arr) {
        $this->iblock = $iblock;
        // select from iblock
        $this->rsLIKE = CIBlockElement::GetList (
            Array("SORT"=>"ASC"),
            Array("IBLOCK_ID" => $iblock, "ACTIVE" => "Y", "ID"=>$see_also_arr, "CATALOG_AVAILABLE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да"),
            false,
            Array("nTopCount" => 5),
            Array("IBLOCK_ID", "ID", "NAME", "PREVIEW_TEXT" , "PREVIEW_PICTURE", "DETAIL_PAGE_URL", "PROPERTY_LIKE", "PROPERTY_NAPISANIE_TOVARA", "PROPERTY_KOMMENTARIY_K_NAPISANIYU", "PROPERTY_ON_MODEL_PHOTO", "PROPERTY_PREVIEW_PHOTO", "PROPERTY_SALE")
        );//было убрано свойство "PROPERTY_F_USER" для предотвращения дубликатов;
		
		/*
		if($_SERVER["REMOTE_ADDR"] == "46.148.203.30"){
			$uniq = [];
			while($LikeProduct = $this->rsLIKE->GetNextElement()) {


				?><pre><?
				print_r($LikeProduct);
				?></pre><?
			}
		}*/
    }

}

?>