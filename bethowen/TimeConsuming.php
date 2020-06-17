<?

$_SERVER['DOCUMENT_ROOT'] = str_replace('/local/cron', '', __DIR__);

ini_set("error_reporting", E_ALL);
set_time_limit(3600);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

/*strange method updates*/
/*
global $DB, $USER_FIELD_MANAGER;

$fields = Array(
   "UF_TIME_WORK_START" => '09:00'
);

$USER_FIELD_MANAGER->Update("CAT_STORE", 1, $fields);
*/

CModule::IncludeModule('sale');

class TimeConsuming {
    public $storesObject;
    public $listStores;
    public $ordersList;
    public $statusOrderList;
    private $hasStatusStart;
    private $hasStatusStop;
    private $day_from;
    private $day_to;

    public function __construct($selectedDaysInterval) {
        $this->listStores = [];
        $this->ordersList = [];
        $this->statusOrderList = [];
        $dfrom = new \DateTime($selectedDaysInterval);
        $date = $dfrom->format('Y-m-d') . " 00:00:00";
        $this->day_from = \Bitrix\Main\Type\DateTime::createFromPhp(\DateTime::createFromFormat("Y-m-d H:i:s", $date ));
        $this->day_to = \Bitrix\Main\Type\DateTime::createFromPhp(\DateTime::createFromFormat("Y-m-d H:i:s", date('Y-m-d') . ' 00:00:00'));
    }

    public function fillStoresProp() {

        //fill object
        $this->getStores();

        if($this->storesObject == null) {
            return false;
        }

        //this for fill data
        while($elements = $this->storesObject->Fetch()) {
            preg_match_all('/\d/', $elements['SCHEDULE'], $time_arr, PREG_PATTERN_ORDER);
            $tm_part = $time_arr[0];
            if(!empty($tm_part)) {
                $tm_arr_shift = [];
                if(in_array($tm_part[0], ['8', '9', '7'])) {
                    $tm_arr_shift = array_merge(['0'], $tm_part);
                    $time_work_st_from = $tm_arr_shift[0] . $tm_arr_shift[1] . ":" . $tm_arr_shift[2] . $tm_arr_shift[3];
                    $time_work_st_to = $tm_arr_shift[4] . $tm_arr_shift[5] . ":" . $tm_arr_shift[6] . $tm_arr_shift[7];
                } else {
                    $time_work_st_from = $tm_part[0] . $tm_part[1] . ":" . $tm_part[2] . $tm_part[3];
                    $time_work_st_to = $tm_part[4] . $tm_part[5] . ":" . $tm_part[6] . $tm_part[7];
                }
                \Bitrix\Catalog\StoreTable::update($elements['ID'], [ 'fields' => ['UF_TIME_WORK_START' => $time_work_st_from, 'UF_TIME_WORK_STOP' => $time_work_st_to] ]);
            } else {
                $time_work_st_from = false;
                $time_work_st_to = false;
            }
            if(preg_match('/Круглосуточно/', $elements['SCHEDULE'])) {
                $time_work_st_from = '00:00';
                $time_work_st_to = '24:00';
                \Bitrix\Catalog\StoreTable::update($elements['ID'], [ 'fields' => ['UF_TIME_WORK_START' => $time_work_st_from, 'UF_TIME_WORK_STOP' => $time_work_st_to] ]);
            }
            if($elements['ID'] == '43') {
                $time_work_st_from = '10:00';
                $time_work_st_to = '22:00';
                \Bitrix\Catalog\StoreTable::update($elements['ID'], [ 'fields' => ['UF_TIME_START_HOLYD' => $time_work_st_from, 'UF_TIME_STOP_HOLYD' => $time_work_st_to, 'UF_NUM_HOLYD' => '6,7'] ]);
            }
            if($elements['ID'] == '74') {
                $time_work_st_from = '10:00';
                $time_work_st_to = '20:00';
                \Bitrix\Catalog\StoreTable::update($elements['ID'], [ 'fields' => ['UF_TIME_START_HOLYD' => $time_work_st_from, 'UF_TIME_STOP_HOLYD' => $time_work_st_to, 'UF_NUM_HOLYD' => '5,6'] ]);
            }
            if($elements['ID'] == '91') {
                $time_work_st_from = '10:00';
                $time_work_st_to = '23:00';
                \Bitrix\Catalog\StoreTable::update($elements['ID'], [ 'fields' => ['UF_TIME_START_HOLYD' => $time_work_st_from, 'UF_TIME_STOP_HOLYD' => $time_work_st_to, 'UF_NUM_HOLYD' => '5,6'] ]);
            }
            if($elements['ID'] == '97') {
                $time_work_st_from = '10:00';
                $time_work_st_to = '24:00';
                \Bitrix\Catalog\StoreTable::update($elements['ID'], [ 'fields' => ['UF_TIME_START_HOLYD' => $time_work_st_from, 'UF_TIME_STOP_HOLYD' => $time_work_st_to, 'UF_NUM_HOLYD' => '5,6'] ]);
            }
            if($elements['ID'] == '102') {
                $time_work_st_from = '10:00';
                $time_work_st_to = '22:00';
                \Bitrix\Catalog\StoreTable::update($elements['ID'], [ 'fields' => ['UF_TIME_START_HOLYD' => $time_work_st_from, 'UF_TIME_STOP_HOLYD' => $time_work_st_to, 'UF_NUM_HOLYD' => '6,7'] ]);
            }
            if($elements['ID'] == '55') {
                $time_work_st_from = '10:00';
                $time_work_st_to = '23:00';
                \Bitrix\Catalog\StoreTable::update($elements['ID'], [ 'fields' => ['UF_TIME_START_HOLYD' => $time_work_st_from, 'UF_TIME_STOP_HOLYD' => $time_work_st_to, 'UF_NUM_HOLYD' => '5,6'] ]);
            }
        }

        return true;
    }

    private function getStores() {
        $this->storesObject = CCatalogStore::GetList(
            array('ID' => 'ASC'),
            array('ACTIVE' => 'Y'),
            false,
            false,
            array(
                'ADDRESS',
                'SCHEDULE',
                'TITLE',
                'ID',
                'UF_TIME_WORK_START',
                'UF_TIME_WORK_STOP',
                'UF_TIME_START_HOLYD',
                'UF_TIME_STOP_HOLYD',
                'UF_NUM_HOLYD',
            )
        );
    }

    public function getListStoresToArr() {

        //fill object
        $this->getStores();

        if($this->storesObject == null) {
            return false;
        }

        while($elements = $this->storesObject->Fetch()) {
            $this->listStores[$elements['ID']] = [
                'TITLE' => $elements['TITLE'],
                'TIME_START' => $elements['UF_TIME_WORK_START'],
                'TIME_STOP' => $elements['UF_TIME_WORK_STOP'],
                'HOLYD_START' => $elements['UF_TIME_START_HOLYD'],
                'HOLYD_STOP' => $elements['UF_TIME_STOP_HOLYD'],
                'HOLY_DAYS' => $elements['UF_NUM_HOLYD'],
            ];
        }
		//var_dump($this->listStores);

        return true;
    }

    public function getOrdersBackMonths() {
 
        $arFilter = Array(
            "SHOW_HISTORY" => "N",
            "DELIVERY_ID" => 2,
            ">DATE_INSERT" => $this->day_from,
            "<DATE_INSERT" => $this->day_to
        );

        $db_sales = CSaleOrder::GetList(
            array("ID" => "DESC"),
            $arFilter
        );

        while($item = $db_sales->Fetch()) {
            $this->ordersList[$item['ID']] = [
                'id' => $item['ID'],
                'idStore' => $this->storeIdObtain($item['ID']),
            ];
        }
    }

    //this not optimal but need get for each element - orderStoreId for self call
    private function storeIdObtain($id) {
        $order_d7 = \Bitrix\Sale\Order::load($id);
        $shipmentCollection = $order_d7->getShipmentCollection();

        foreach ($shipmentCollection as $shipment) {

            $idStore = $shipment->getStoreId();
            if($idStore != 0) {
                $outStr = $idStore;
            }
        }

        return $outStr;
    }

    public function selectOrderStatusChanges($order = null) {

        global $DB;
        if($order == null) {
            $back_to_12 = $this->day_from;
            //select all massive data for decrease load on MySQL
            $result = $DB->Query("SELECT `ID`, `ORDER_ID`, `TYPE`, `DATE_CREATE`, `DATA` FROM `b_sale_order_change` WHERE TYPE='ORDER_STATUS_CHANGED' AND DATE_CREATE > '" . $back_to_12->add("-12 hours")->format('Y-m-d') . "' AND DATE_CREATE < '" . $this->day_to->format('Y-m-d') . "'");
        } else {
            $result = $DB->Query("SELECT `ID`, `ORDER_ID`, `TYPE`, `DATE_CREATE`, `DATA` FROM `b_sale_order_change` WHERE TYPE='ORDER_STATUS_CHANGED' AND ORDER_ID='".$order."'");
        }

        while($row = $result->Fetch()) {
            $this->statusOrderList[] = [
                'ORDER_ID' => $row['ORDER_ID'],
                'STATUS_ID' => unserialize($row['DATA'])['STATUS_ID'],
                'TIME' => $row['DATE_CREATE']
            ];
        }

        $DB->Disconnect();
    }
    //save to DB statistic
    public function saveToDbStatistic($arrDa) {
        $highload_store = new \Bethowen\Helpers\WorkerHLBlock(HILOAD_STATISTIC_COMPOSE);
        unset($total);
        foreach($arrDa as $keys_direct => $val) {

            $id = intval(explode('_', $keys_direct)[0]);
            $date = \Bitrix\Main\Type\DateTime::createFromPhp(\DateTime::createFromFormat("Y-m-d", explode('_', $keys_direct)[1]));

            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/test/file_data_statistic.txt', $id . ' ' . serialize($val) . PHP_EOL, FILE_APPEND);
            $total = array_sum($val) / count($val);

            $data_push = array(
                'UF_DATE_STATISTICS' => $date,
                'UF_NAME_STORE' => str_replace('Остатки в магазине', 'Магазин', $this->listStores[$id]['TITLE']),
                'UF_ID_STORE' => $id,
                'UF_COMPOSE_TIME' => $total,
            );

            $highload_store->pushNewData($data_push);
        }
    }

    public function checkOrderTime($markTimeStart, $markTimeStop) {
        $dayS = $markTimeStart->format('d');
        $daySop = $markTimeStop->format('d');
        if($dayS != $daySop) {
            return false;
        }
        if($dayS == $daySop) {
            return true;
        }
    }

    public function caculateDiffForOrder($order, $start_str, $stop_str) {
        //print_r($order);
        $start = \DateTime::createFromFormat("Y-m-d H:i:s", $start_str);
        $stop = \DateTime::createFromFormat("Y-m-d H:i:s", $stop_str);
        $intervalObj = $start->diff($stop);
        $toDay = $this->checkOrderTime($start, $stop);
        $idShop = $this->ordersList[$order]['idStore'];
        $difftime = [];
        if($toDay != true) {
            $obj1 = $this->recalculateTime($start, $idShop, 'start');
            $obj2 = $this->recalculateTime($stop, $idShop, 'stop');
            $difftime = [
                'shop' => $idShop,
                'time_spend' => ($obj2->h + $obj1->h) * 3600 + ($obj2->i + $obj1->i) * 60 + ($obj2->s + $obj1->s),
                'date' => $start->format('Y-m-d'),
            ];
        } else {
            $time_night = $this->checkerTimeBound($start, $idShop);

            if($time_night) {
                $time_final = ($intervalObj->h * 3600 + $intervalObj->i * 60 + $intervalObj->s) - ($time_night->h * 3600 + $time_night->i * 60 + $time_night->s);
            } else {
                $time_final = $intervalObj->h * 3600 + $intervalObj->i * 60 + $intervalObj->s;
            }

            if($time_final < 0) {
                $time_final = 0;
            }

            $difftime = [
                'shop' => $idShop,
                'time_spend' => $time_final,
                'date' => $start->format('Y-m-d'),
            ];
        }
        return $difftime;
    }

    private function recalculateTime($markTime, $idShop, $switch) {
        if(preg_match('/' . $markTime->format('N') . '/', $this->listStores[$idShop]['HOLY_DAYS'])) {
            $time_horus = ($switch == 'start') ? $this->listStores[$idShop]['HOLYD_STOP'] : $this->listStores[$idShop]['HOLYD_START'];
        } else {
            $time_horus = ($switch == 'start') ? $this->listStores[$idShop]['TIME_STOP'] : $this->listStores[$idShop]['TIME_START'];
        }
        return $markTime->diff(\DateTime::createFromFormat("Y-m-d H:i:s", $markTime->format('Y-m-d') . ' ' . $time_horus . ":00"));
    }

    private function checkerTimeBound($markTime, $idShop) {
        if(preg_match('/' . $markTime->format('N') . '/', $this->listStores[$idShop]['HOLY_DAYS'])) {
            $time_horus = $this->listStores[$idShop]['HOLYD_START'];
        } else {
            $time_horus = $this->listStores[$idShop]['TIME_START'];
        }

        $time_start_shop = \DateTime::createFromFormat("Y-m-d H:i:s", $markTime->format('Y-m-d') . ' ' . $time_horus . ":00");

        if ($time_start_shop > $markTime) {
            return $time_start_shop->diff($markTime);
        } else {
            return false;
        }
    }

    //this we check how long order compose for each order
    public function caluclateCompositionOrder($id_order) {

        $this->hasStatusStart = false;
        $this->hasStatusStop = false;

        if(empty($this->ordersList)) {
            return false;
        }

        if(empty($this->statusOrderList)) {
            return false;
        }

        $store = $this->ordersList[$id_order]['idStore'];

        foreach($this->statusOrderList as $key => $value) {
            if(array_keys($value, $id_order)) {
                $keys_poiter[] = $key;
            }
        }

        if(count($keys_poiter) < 2) {
            return false;
        }

        $time_bound = [];
        unset($time_s, $time_o);
        foreach($keys_poiter as $key) {
            if($this->statusOrderList[$key]['STATUS_ID'] == "S") {

                $time_s = \DateTime::createFromFormat("Y-m-d H:i:s", $this->statusOrderList[$key]['TIME']);
                if(isset($time_o) && $time_s > $time_o) {
                    continue;
                }

                $time_bound[$this->statusOrderList[$key]['ORDER_ID']]["START"] = $this->statusOrderList[$key]['TIME'];
                $this->hasStatusStart = true;
            }
            if($this->statusOrderList[$key]['STATUS_ID'] == "R") {
                foreach($keys_poiter as $key_) {
                    if($this->statusOrderList[$key_]['STATUS_ID'] == "S") {
                        $time_bound[$this->statusOrderList[$key_]['ORDER_ID']]["BEFORE_MID"] = $this->statusOrderList[$key_]['TIME'];
                        break;
                    }
                    $time_bound[$this->statusOrderList[$key]['ORDER_ID']]["MID"] = $this->statusOrderList[$key]['TIME'];
                }
                $time_bound[$this->statusOrderList[$key]['ORDER_ID']]["MID"] = $this->statusOrderList[$key]['TIME'];
            }
            if($this->statusOrderList[$key]['STATUS_ID'] == "O") {
                $time_bound[$this->statusOrderList[$key]['ORDER_ID']]["STOP"] = $this->statusOrderList[$key]['TIME'];
                $this->hasStatusStop = true;
                $time_o = \DateTime::createFromFormat("Y-m-d H:i:s", $this->statusOrderList[$key]['TIME']);
            }
        }

        if(($this->hasStatusStart * $this->hasStatusStop) == false) {
            return false;
        }
        //this we check if we have correction

        $difftime = [];

        foreach($time_bound as $key => $time) {
            $difftime[$key] = $this->caculateDiffForOrder($key, $time['START'], $time['STOP']);
            //this we check if exist middle time - correction - we calculate add time
            if(isset($time['MID'])) {
                $difftime[$key . '_1'] = $this->caculateDiffForOrder($key, $time['BEFORE_MID'], $time['MID']);
            }
        }

        return $difftime;
    }
}

$time_cons = new TimeConsuming('-1 days');

$time_cons->getListStoresToArr();

$time_cons->getOrdersBackMonths();
$time_cons->selectOrderStatusChanges();

$massive_consuming = [];
$i = 0;
foreach ($time_cons->ordersList as $key => $val) {
    $tmp = $time_cons->caluclateCompositionOrder($val['id']);
    if($tmp != false) {
        foreach($tmp as $key => $val) {
            $massive_consuming[$key] = $val;
        }
    }
    $i++;
}

$statistic = [];
foreach($massive_consuming as $key => $value) {
    $statistic[$value['shop'] . '_' . $value['date']][] = $value['time_spend'];
}

$time_cons->saveToDbStatistic($statistic);

unset($time_cons->statusOrderList);
unset($time_cons->ordersList);
unset($time_cons->listStores);
unset($statistic);
