<?php

include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

define('IBLOCK_ID', '60');

if(isset($_FILES['file'])) {
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];

    $tmp_path =  $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . substr(md5($file_name), 0, 3) . '/' . rand(100, 999) . '_' . $file_name;
    if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . substr(md5($file_name), 0, 3) . '/')) {
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . substr(md5($file_name), 0, 3) . '/');
        chmod($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . substr(md5($file_name), 0, 3) . '/', 0775);
    }

    if ( 0 < $_FILES['file']['error'] ) {
        echo json_encode([ 'err' => 'Error: ' . $_FILES['file']['error'], 'result' => '0']);
        exit();
    } else {
        move_uploaded_file($file_tmp, $tmp_path);
    }

	$path_parts = pathinfo($tmp_path);
	$allow_ext = ['doc', 'docx', 'pdf', 'pptx', 'ppt', 'xls', 'xlsx'];
	if(!in_array($path_parts['extension'], $allow_ext)) {
		echo json_encode([ 'err' => 'ext error', 'result' => '0' ]);
		exit();
	}
	if((filesize($tmp_path) / 1000000) > 15.0) {
        echo json_encode([ 'err' => 'size error', 'result' => '0' ]);
        exit();
	}

    $_SESSION['file_atach_vacancy'] = $tmp_path;

    echo json_encode([ 'err' => [], 'result' => '1' ]);
    exit();
}

\Bitrix\Main\Loader::includeModule('iblock');

$hiload = new Bethowen\Helpers\WorkerHLBlock(HLBL_VAC);

$stores_full = [];
//need this move or Session or more better Cached it (or use memcached)
//--> Cached
$d_st = $hiload->selectData(array('UF_NAME', 'UF_SHOP_ID', 'UF_XML_ID', 'ID', 'UF_ADDRESS', 'UF_UNDERGROUND', 'UF_COLOR_M'));

while($data = $d_st->Fetch()) {
    $stores_full[$data['UF_XML_ID']] = $data;
}

$out_massive_search = [];

$dataIBlock = CIBlockElement::GetList(
    array('SORT' => 'ASC'),
    array('IBLOCK_ID' => IBLOCK_ID, 'ACTIVE' => 'Y'),
    false,
    false,
    array('ID', 'NAME', 'PROPERTY_STORE', 'PROPERTY_COUNT')
);
//--> Cached

if($_REQUEST['type'] == 'sendMessHR') {
    
    $dataIBlock = CIBlockElement::GetList(
        array('SORT' => 'ASC'),
        array('IBLOCK_ID' => IBLOCK_ID, 'ACTIVE' => 'Y', 'ID' => $_REQUEST['pos']),
        false,
        false,
        array('ID', 'NAME', 'PROPERTY_STORE', 'PROPERTY_COUNT')
    );
    $massData = array(
        'NAME' => $_REQUEST['name'],
        'PHONE' => $_REQUEST['phone'],
        'EMAIL' => $_REQUEST['email'],
        'SHOP' => $stores_full[$_REQUEST['shop']]['UF_ADDRESS'],
        'POSITION' => $dataIBlock->Fetch()['NAME'],
    );

	\Bitrix\Main\Mail\Event::send([
		"EVENT_NAME" => "SEND_MAIL_ABOUT_RECRUITER",
		"LID" => "s1",
		"C_FIELDS" => $massData,
		"FILE" => [
			\Bitrix\Main\IO\Path::ConvertLogicalToPhysical($_SESSION['file_atach_vacancy'])
		],
		"MESSAGE_ID" => 166
	]);
	unset($_SESSION['file_atach_vacancy']);

    echo json_encode(['ready' => 'ok']);
    exit();
}

if($_REQUEST['type'] == 'search') {
    //echo "<pre>";
    while($data = $dataIBlock->Fetch()) {
        //print_r($data);
        $metro = '';
        if(!empty($stores_full[$data['PROPERTY_STORE_VALUE']]['UF_UNDERGROUND'][0])) {
            $metro = ' / <div ' . ((!empty($stores_full[$data['PROPERTY_STORE_VALUE']]['UF_COLOR_M'])) ? 'style="background-color:' . $stores_full[$data['PROPERTY_STORE_VALUE']]['UF_COLOR_M'] . '"' : 'style="background-color:#fff;color:#000;"') . ' class="icon-metro">M</div> ' . $stores_full[$data['PROPERTY_STORE_VALUE']]['UF_UNDERGROUND'][0];
        }
        
        $out_massive_search[] = [
            'txt' => $data['NAME'] . $metro . ' / ' . preg_replace("/г.\sМосква,\s/", '', $stores_full[$data['PROPERTY_STORE_VALUE']]['UF_ADDRESS']),
            'name' => $data['NAME'],
            'id_e' => $data['ID'],
            'id_store' => $stores_full[$data['PROPERTY_STORE_VALUE']]['ID'],
            'store_xml_id' => $data['PROPERTY_STORE_VALUE'],
        ];
    }

    //filter
    $filtered = [];
    foreach($out_massive_search as $key => $value) {
        if(preg_match('/' .  strtolower($_REQUEST['search']) . '/i', strtolower($value['txt']))) {
            $filtered[] = $value;
        }
    }

    echo json_encode($filtered, JSON_UNESCAPED_UNICODE);
}

if($_REQUEST['type'] == 'getItem') {
    while($data = $dataIBlock->Fetch()) {
        if($data['ID'] == $_REQUEST['item']) {
            $metro = '';
            if(!empty($stores_full[$data['PROPERTY_STORE_VALUE']]['UF_UNDERGROUND'][0])) {
                $metro = 'м. ' . $stores_full[$data['PROPERTY_STORE_VALUE']]['UF_UNDERGROUND'][0] . ' / ';
            }
            $out_search['shops'][] = [
                'xml_id' => $data['PROPERTY_STORE_VALUE'],
                'address' => $metro . $stores_full[$data['PROPERTY_STORE_VALUE']]['UF_ADDRESS'],
                'id' => $stores_full[$data['PROPERTY_STORE_VALUE']]['UF_SHOP_ID'],
            ];
            $out_search['name'] = $data['NAME'];
            $out_search['id'] = $data['ID'];
        }
    }

    echo json_encode($out_search, JSON_UNESCAPED_UNICODE);
}

if($_REQUEST['type'] == 'getShop') {
    while($data = $dataIBlock->Fetch()) {
        if($data['ID'] == $_REQUEST['item'] && $data['PROPERTY_STORE_VALUE'] == $_REQUEST['shop']) {
            $metro = '';
            if(!empty($stores_full[$data['PROPERTY_STORE_VALUE']]['UF_UNDERGROUND'][0])) {
                $metro = 'м. ' . $stores_full[$data['PROPERTY_STORE_VALUE']]['UF_UNDERGROUND'][0] . ' / ';
            }
            $out_search['shops'][] = [
                'xml_id' => $data['PROPERTY_STORE_VALUE'],
                'address' => $metro . $stores_full[$data['PROPERTY_STORE_VALUE']]['UF_ADDRESS'],
                'id' => $stores_full[$data['PROPERTY_STORE_VALUE']]['UF_SHOP_ID'],
            ];
            $out_search['name'] = $data['NAME'];
            $out_search['id'] = $data['ID'];
        }
    }

    echo json_encode($out_search, JSON_UNESCAPED_UNICODE);
}

if($_REQUEST['type'] == 'getCount') {

    $vac_num = [];
    while($data = $dataIBlock->Fetch()) {
        $vac_num[$data['ID']] = $data['PROPERTY_COUNT_VALUE'];
    }

    echo json_encode(['count' => array_sum(array_values($vac_num))]);
}
