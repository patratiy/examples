<?php

ini_set('display_errors', '1');
ini_set('error_reporting', 'E_ALL');

$_SERVER["DOCUMENT_ROOT"] = "/var/www2/jqestate/httpdocs";

if(isset($_GET['action']) && $_GET['action'] == "resized") {
    require($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_before.php');
    set_time_limit(0);
}

ini_set('memory_limit','1024M');


$articls = new SimpleXMLElement(file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/service-script/ExportArticle_'));

$c = 0;
if(isset($_GET['action']) && $_GET['action'] == "resized") {
    CModule::IncludeModule("iblock");
    CModule::IncludeModule("main");
    global $DB;
    //
    $elements = CIBlockElement::GetList(
        array('ID'=>'ASC'),
        array('IBLOCK_ID' => '5', "ACTIVE" => "Y"),
        false,
        false,
        array('ID', 'NAME', 'CODE', 'IBLOCK_ID', 'SORT', 'PROPERTY_OLD_SYSTEM_ID')
    );
    $elements_ids = array();
    while($data_ext = $elements->GetNextElement()) {
        $f = $data_ext->GetFields();
        $prop = $data_ext->GetProperties();
        $elements_ids[$prop[OLD_SYSTEM_ID][VALUE]] = $f[ID];
    }
}

$c1 = 0;
$list_updated = '';


$handle = fopen($_SERVER['DOCUMENT_ROOT'] . '/upload/img_need_to_download.csv', "r");
while (($line = fgets($handle)) !== false) {
    $array_old_ids[] = explode(';', $line)[0];
}
fclose($handle);

foreach ($articls->offer as $arOffer){
    $arOffer = (array) $arOffer;
    // !in_array() when need continue update massive images
    if(!in_array($arOffer['Article'], $array_old_ids)) {
        if((isset($argv[1]) && $c == $argv[1]) || (isset($_GET['limit']) && $c == $_GET['limit'])) {
            break;
        }
        
        $c1++;
    	
    	if ($argv[2] == 'do_load_image') {
        	$images = (array)$arOffer['Images'];

            $download_f = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . $arOffer['Article'];
            if (!file_exists($download_f)) {
                mkdir($download_f);
            }
            chdir($download_f);
            $elements_in_folder = scandir($download_f);
        
            if (is_array($images[Image])) {
                foreach($images[Image] as $item) {
                   
                    $parseUrl = parse_url($item);
            	    $newUrl = "http://" . $parseUrl['host'] . "/originals" . str_replace("-jqestate-1024", "",$parseUrl['path']);
            	    $parts_new_url = explode('/', $item);
            	    if (!in_array($parts_new_url[count($parts_new_url) - 1] . '.jpg', $elements_in_folder)) {
            	        if ($argv[3] == 'small') {
                            shell_exec('wget ' . $newUrl);
                        }
                        if ($argv[3] == 'full') {
                            shell_exec('wget ' . str_replace('https', 'http', $item));
                        }
                    }
                }
            }
            else {
                $parseUrl = parse_url($images[Image]);
        	    $newUrl = "http://" . $parseUrl['host'] . "/originals" . str_replace("-jqestate-1024", "",$parseUrl['path']);
        	    $parts_new_url = explode('/', $newUrl);
        	    if (!in_array($parts_new_url[count($parts_new_url) - 1] . '.jpg', $elements_in_folder)) {
        	        if ($argv[3] == 'small') {
                        shell_exec('wget ' . $newUrl);
                    }
                    if ($argv[3] == 'full') {
                        shell_exec('wget ' . str_replace('https', 'http', $images[Image]));
                    }
                }
            }
            
            $elements_in_folder = scandir($download_f);

        	print_r($elements_in_folder);

        	foreach($elements_in_folder as $item) {
        	    //echo $item;
        	    if($item != '.' && $item != '..') {
        	        $new_path = $download_f . '/' . $item . '.jpg';
        	        if(!is_dir($download_f . '/' . $item) && strstr($item, '.jpg') == false) {
        	            rename($download_f . '/' . $item, $download_f . '/' . $item . '.jpg');
        	        }
        	    }
        	}
        	if (!file_exists($download_f . '/resized/')) {
        	    mkdir($download_f . '/resized/');
        	    chmod($download_f . '/resized/', 0777);
            }
        }
        if(isset($_GET['action']) && $_GET['action'] == "resized") {

            CIBlockElement::SetPropertyValuesEx($elements_ids[$arOffer['Article']], OBJECTS_IBLOCK_ID, array("OBJECT_PICTURES_MINI" => array('VALUE' => array('del' => 'Y'))));
            $download_f = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . $arOffer['Article'];
            $elements_in_folder = scandir($download_f);
            
            unset($arProperty['OBJECT_PICTURES_MINI']);
            foreach($elements_in_folder as $item) {
        	    if($item != '.' && $item != '..' && !is_dir($download_f . '/' . $item)) {
        	        $path_src = $download_f . '/' . $item;
        	        //$arr_new_path = explode('/', $new_path);
        	        $split_name = explode('.',$item);
        	        $image_small = $download_f . '/resized/' . $split_name[0] . '_small.jpg';
        	        unlink($image_small);
        	        usleep(1000);
        	        if (!file_exists($image_small)) {
            	        $file_resize = CFile::ResizeImageFile(
                            $p = $path_src,
                            $pd = $image_small,
                            $s = array('width'=>600,'height'=>600),
                            $m = BX_RESIZE_IMAGE_PROPORTIONAL,
                            $wm = array(),
                            $qu = "95",
                            $af = false
                        );
                    }
                    //echo $file_resize;
                    $arProperty['OBJECT_PICTURES_MINI'][] = CFile::MakeFileArray($image_small);
        	    }
        	}
        	$list_updated .= $arOffer['Article'] . "\n";

        	CIBlockElement::SetPropertyValuesEx($elements_ids[$arOffer['Article']], OBJECTS_IBLOCK_ID, $arProperty);
        }
    }
    $c++;
}
if(isset($_GET['action']) && $_GET['action'] == "resized") {
    $prev_text = file_get_contents('/var/www/html/service-script/updated.txt');
    file_put_contents('/var/www/html/service-script/updated.txt', $prev_text . $list_updated);
}

echo $c1;

?>