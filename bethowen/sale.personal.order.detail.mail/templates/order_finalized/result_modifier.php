<?

if(!function_exists('toStringM')) {
    function toStringM($format) {
        $months = [
            '01' => 'января',
            '02' => 'февраля',
            '03' => 'марта',
            '04' => 'апреля',
            '05' => 'мая',
            '06' => 'июня',
            '07' => 'июля',
            '08' => 'августа',
            '09' => 'сентября',
            '10' => 'октября',
            '11' => 'ноября',
            '12' => 'декабря',
        ];
        $m = explode('.', $format);
        return $m[0] . ' ' . $months[$m[1]] . (!empty($m[2]) ? ' ' . $m[2] : '');
    }
}

$del_ids_pvz = [];
$del_ids_bethowen = [];
$del_ids_courier = [];

foreach(Bitrix\Sale\Delivery\Services\Manager::getActiveList() as $key => $value) {
    if(preg_match('/пвз/', strtolower($value['NAME']))) {
        $del_ids_pvz[] = $value['ID'];
    }
    if(preg_match('/самовы/', strtolower($value['NAME']))) {
        $del_ids_bethowen[] =  $value['ID'];
    }
    if(preg_match('/курьер/', strtolower($value['NAME']))) {
        $del_ids_courier[] = $value['ID'];
    }
}

global $USER;

foreach($arResult['ORDER_PROPS'] as $key => $value) {
    if($value['CODE'] == 'EMAIL') {
        $arResult['EMAIL'] = $value['VALUE'];
    }
    if($value['CODE'] == 'PERSONAL_PHONE') {
        $arResult['PERSOANL_PHONE'] = $value['VALUE'];
    }
    if($value['CODE'] == 'LAST_NAME') {
        $arResult['LAST_NAME'] = $value['VALUE'];
    }
    if($value['CODE'] == 'NAME') {
        $arResult['FIRST_NAME'] = $value['VALUE'];
    }
    if($value['CODE'] == 'POINT_OF_ISSUE_ID_ADDRESS') {
        $arResult['ADDRESS_PICK_POINT'] = $value['VALUE'];
    }
    if($value['CODE'] == 'DATE4DELIVERY') {
        $arResult['DATE_DELIVERY_COURIER'] = $value['VALUE'];
    }
    if($value['CODE'] == 'DELIVERY_TIME_INTERVA') {
        $arResult['TIME_DELIVERY_COURIER'] = $value['VALUE'];
    }
    if($value['CODE'] == 'STREET') {
        $arResult['DELIVERY_OWN_STREET'] = $value['VALUE'];
    }
    if($value['CODE'] == 'HOUSE') {
        $arResult['DELIVERY_OWN_HOUSE'] = $value['VALUE'];
    }
    if($value['CODE'] == 'KVARTIRA') {
        $arResult['DELIVERY_OWN_ROOM'] = $value['VALUE'];
    }
    if($value['CODE'] == 'ETAJ') {
        $arResult['DELIVERY_OWN_FLOOR'] = $value['VALUE'];
    }
    if($value['CODE'] == 'DISCOUNT_CARD') {
        $arResult['DISCOUNT_CARD_PERSONAL'] = $value['VALUE'];
    }
}
$arResult['FULL_NAME'] = $arResult['FIRST_NAME'] . ' ' . $arResult['LAST_NAME'];

if(isset($arResult['DISCOUNT_CARD_PERSONAL']) && !empty($arResult['DISCOUNT_CARD_PERSONAL']) && preg_match('/bethowen.ru/', $arResult['EMAIL'])) {
    $key_c = sha1('email_' . $arResult['ID']);
    $cache = new \Bethowen\Helpers\FasterClaster('email_triger');

    if($cache->getData($key_c) != null) {
         $arResult['EMAIL'] = $cache->getData($key_c);
    }
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/test/memcached.txt', $cache->getData($key_c).PHP_EOL, FILE_APPEND);
}

//stores Info
$arElement = $arResult['DELIVERY']['STORE_LIST'][$arResult['SHIPMENT'][0]['STORE_ID']];

$date_create = \DateTime::createFromFormat('d.m.Y H:i:s', $arResult['DATE_INSERT']);

$arResult['DATE_CREATE_FORMAT'] = $date_create->format('d.m.Y') . ' г.';

ob_start();
if(in_array($arResult['SHIPMENT'][0]['DELIVERY_ID'], $del_ids_pvz)) {
?>
<tr>
    <td style="vertical-align: top;width: 40%; padding-right: 5px;">
        <b>Самовывоз</b>
    </td>
    <td style="vertical-align: top;padding-bottom: 25px;line-height: 1.4;">
        <?=$arResult['SHIPMENT'][0]['DELIVERY_NAME'];?><br>
        <?=$arResult['ADDRESS_PICK_POINT'];?><br>
        Можно забрать до <?=toStringM($arResult['DATE_INSERT']->add("7 day")->format('d.m'));?>
    </td>
</tr>
<?
}
if(in_array($arResult['SHIPMENT'][0]['DELIVERY_ID'], $del_ids_bethowen)) {
?>
<tr>
    <td style="vertical-align: top;width: 40%; padding-right: 5px;">
        <b>Самовывоз</b>
    </td>
    <td style="vertical-align: top;padding-bottom: 25px;line-height: 1.4;">
        <?=str_replace('Самовывоз', '', $arResult['SHIPMENT'][0]['DELIVERY_NAME']);?>:<br>
		<?if(!empty($arElement)): ?>
        <table>
            <tr>
                <td><?=$arElement['ADDRESS'];?></td>
                <td style="vertical-align: top; padding: 4px;"><a href="https://www.bethowen.ru/shops/<?=$arElement['ID']?>/" target="_blank" title="Смотреть на карте"><img src="https://gallery.retailrocket.net/558a6ce06636b43e24aaf5cf/%d0%91%d0%b5%d0%b7%d1%8b%d0%bc%d1%8f%d0%bd%d0%bd%d1%8b%d0%b9-2_%d0%9c%d0%be%d0%bd%d1%82%d0%b0%d0%b6%d0%bd%d0%b0%d1%8f%20%d0%be%d0%b1%d0%bb%d0%b0%d1%81%d1%82%d1%8c%201.png" style="height:19px;"></a></td>
            </tr>
        </table>
		<? endif; ?>
        <!--+7 495 9843122<br>-->
        Можно забрать до <?=toStringM($arResult['DATE_INSERT']->add("3 day")->format('d.m'));?>
    </td>
</tr>
<?
}
if( in_array($arResult['SHIPMENT'][0]['DELIVERY_ID'], $del_ids_courier) ) {
?>
<tr>
    <td style="vertical-align: top;width: 40%; padding-right: 5px;">
        <b>Доставка курьером</b>
    </td>
    <td style="vertical-align: top;padding-bottom: 25px;line-height: 1.4;">
        <?=str_replace('Доставка курьером', '', $arResult['SHIPMENT'][0]['DELIVERY_NAME']);?><br>
        Адрес доставки: <?=$arResult['DELIVERY_OWN_STREET']?>, д. <?=$arResult['DELIVERY_OWN_HOUSE'];?><?=(!empty($arResult['DELIVERY_OWN_ROOM']) ? ', кв. ' . $arResult['DELIVERY_OWN_ROOM'] : '');?><?=(!empty($arResult['DELIVERY_OWN_ROOM']) ? ', эт. ' . $arResult['DELIVERY_OWN_FLOOR'] : '');?><br>
        <!--+7 495 9843122<br>-->
        <div style="margin-top: 5px;">
            Время доставки:
            <?=toStringM($arResult['DATE_DELIVERY_COURIER']);?>,
            <div style="display:inline-block;"><?=strtolower($arResult['TIME_DELIVERY_COURIER']);?></div>
        </div>
    </td>
</tr>
<?
}

$data_delivery = ob_get_clean();

$arResult['SHIPMENT_INFO'] = $data_delivery;

foreach($arResult['BASKET'] as $key => $value) {
    $file = pathinfo($_SERVER['DOCUMENT_ROOT'] . $value['PICTURE']['SRC']);
    if(!file_exists($_SERVER["DOCUMENT_ROOT"] . '/upload/email/' . substr($file['basename'], 0, 3) . '/')) {
        mkdir($_SERVER["DOCUMENT_ROOT"] . '/upload/email/' . substr($file['basename'], 0, 3) . '/');
        chmod($_SERVER["DOCUMENT_ROOT"] . '/upload/email/' . substr($file['basename'], 0, 3) . '/', 0775);
    }

    if(!file_exists($_SERVER["DOCUMENT_ROOT"] . '/upload/email/' . substr($file['basename'], 0, 3) . '/' . sha1($file['basename']) . '.jpg')) {
        $file_resize = CFile::ResizeImageFile(
            $p = ($_SERVER['DOCUMENT_ROOT'] . $value['PICTURE']['SRC']),
            $pd = ($_SERVER["DOCUMENT_ROOT"] . '/upload/email/' . substr($file['basename'], 0, 3) . '/' . sha1($file['basename']) . '.jpg'),
            $s = array('width'=>124,'height'=>124),
            $m = BX_RESIZE_IMAGE_PROPORTIONAL,
            $wm = array(),
            $qu = "92",
            $af = false
        );
    } else {
        $pd = $_SERVER["DOCUMENT_ROOT"] . '/upload/email/' . substr($file['basename'], 0, 3) . '/' . sha1($file['basename']) . '.jpg';
    }
    
    $arResult['BASKET_IMG'][$key]['IMG'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $pd);
    
    if(empty($value['PICTURE']['SRC'])) {
        $arResult['BASKET_IMG'][$key]['IMG'] = '/images/no_photo_medium.png';
    }
}

?>
