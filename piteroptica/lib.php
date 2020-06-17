<?php
//produnction
//piteroptika
//ec4cb9ccd89fa5544b9b0a7e4bbb19b6
//IM 236bb329-b54c-441b-86a7-46650cdbf30c

namespace Helpers;

define('IBLOCK_MODIFICATION', 9);
//CATALOG_GROUP_ID for one price this by default is 1
define('CATALOG_GROUP_ID', 1);
define('HLBL_BRAND', 2);
define('HLBL_COLOR', 1);
define('HLBL_MATERIAL', 4);
define('IBLOCK_GOODS', 5);


use Bitrix\Main\Loader;
Loader::includeModule("iblock");
Loader::includeModule("catalog");

//helpers as Kirill Mayorov used
//some practic function


function remapArrayValues( $map, $array )
{
  $mapped = array();

  foreach( $array as $key => $value ) {
    if( !array_key_exists($key, $map) )
    {
      $escapedKey = str_replace('\'', '\\\'', $key);
      throw new \Exception("Map has not contain the key: {$escapedKey}'!");
      break;
    }
    $mapped[ $map[$key] ] = $value;
  }

  return $mapped;
}


//word search modification
class nameModifier {
    private $NAME_PARTS;
    public $searchable;
    private $COUNT_PARTS;

    public function __construct() {

    }

    public function nameProvider($name) {
        $this->NAME_PARTS = explode(' ', strtoupper(trim($name, ' ')));
        $this->COUNT_PARTS = count($this->NAME_PARTS);
        $this->searchable = null;
    }

    protected function secondWordCorrection($w_second) {
        if(preg_match('/GU/', $w_second) && preg_match('/\d+/', $w_second)) {
            $seria = preg_split('/[\D]+/', $w_second);
            return 'GU ' . $seria[1];
        }
        elseif(preg_match('/v-/', $w_second) && preg_match('/\d+/', $w_second)) {
            $seria = preg_split('/[\D]+/', $item);
            return 'V-' . $seria[1];
        }
        elseif(preg_match('/S /', $w_second) && preg_match('/\d+/', $w_second)) {
            $seria = preg_split('/[\D]+/', $item);
            return 'S-' . $seria[1];
        }
        elseif(preg_match('/D/', $w_second) && preg_match('/\d+/', $w_second) && !preg_match('/\s/', $w_second)) {
            $seria = preg_split('/[\D]+/', $w_second);
            return 'D ' . $seria[1];
        }
        else {
            return $w_second;
        }
    }

    //!this method need in future to be improve  
    //return none. apply result to public value $searchable
    public function makeNamePrepare() {
        if($this->COUNT_PARTS == 2) {
            $this->searchable = $this->NAME_PARTS[0] . ' ' . $this->secondWordCorrection($this->NAME_PARTS[1]);
        }
        if($this->COUNT_PARTS > 2) {
            $this->searchable = $this->NAME_PARTS[0] . ' ' . $this->secondWordCorrection($this->NAME_PARTS[1]) . ' ' . $this->NAME_PARTS[2];
        }
        if($this->COUNT_PARTS > 3) {
            $this->searchable = $this->NAME_PARTS[0] . ' ' . $this->secondWordCorrection($this->NAME_PARTS[1]) . ' ' . $this->NAME_PARTS[2] . ' ' . $this->NAME_PARTS[3];
            if(preg_match('/\(/', $this->NAME_PARTS[3])) {
                $this->searchable = $this->NAME_PARTS[0] . ' ' . $this->secondWordCorrection($this->NAME_PARTS[1]) . ' ' . $this->NAME_PARTS[2];
            }
            if(preg_match('/RAY/', $this->NAME_PARTS[0]) && preg_match('/\D+/', $this->NAME_PARTS[0]) && !preg_match('/\-/', $this->NAME_PARTS[0])) {
                $this->searchable = $this->NAME_PARTS[0] . '-' . $this->NAME_PARTS[1] . ' ' . $this->NAME_PARTS[2] . ' ' . $this->NAME_PARTS[3] . ' ' . $this->NAME_PARTS[4];
            }
            if(preg_match('/AIR/', $this->NAME_PARTS[0]) &&  preg_match('/OPTIX/', $this->NAME_PARTS[1])) {
                $this->searchable = $this->NAME_PARTS[0] . ' ' . $this->NAME_PARTS[1] . ' ' . $this->NAME_PARTS[2] . ' ' . $this->NAME_PARTS[3] . ' ' . $this->NAME_PARTS[4];
            }
            if(preg_match('/ACUVUE/', $this->NAME_PARTS[0]) &&  preg_match('/OASYS/', $this->NAME_PARTS[1])) {
                $this->searchable = $this->NAME_PARTS[0] . ' ' . $this->NAME_PARTS[1] . ' ' . $this->NAME_PARTS[2] . ' ' . $this->NAME_PARTS[3] . ' ' . $this->NAME_PARTS[4];
            }
            if(preg_match('/ACUVUE/', $this->NAME_PARTS[1])) {
                $this->searchable = $this->NAME_PARTS[0] . ' ' . $this->NAME_PARTS[1] . ' ' . $this->NAME_PARTS[2] . ' ' . $this->NAME_PARTS[3] . ' ' . $this->NAME_PARTS[4];
            }
            if(preg_match('/DIAG/', $this->NAME_PARTS[0]) && preg_match('/ACUVUE/', $this->NAME_PARTS[2])) {
                $this->searchable = $this->NAME_PARTS[0] . ' ' . $this->NAME_PARTS[1] . ' ' . $this->NAME_PARTS[2] . ' ' . $this->NAME_PARTS[3] . ' ' . $this->NAME_PARTS[4];
            }
            if(preg_match('/ACUVUE/', $this->NAME_PARTS[0]) && preg_match('/OASYS/', $this->NAME_PARTS[1]) && (preg_match('/HYDRACLEAR/', $this->NAME_PARTS[3]) || preg_match('/ASTIGMATISM/', $this->NAME_PARTS[3]))) {
                $this->searchable = $this->NAME_PARTS[0] . ' ' . $this->NAME_PARTS[1] . ' ' . $this->NAME_PARTS[2] . ' ' . $this->NAME_PARTS[3] . ' ' . $this->NAME_PARTS[4] . ' ' . $this->NAME_PARTS[5];
            }
        }

    }
}


function antidebmes($array, $display = false, $output_f = 'print') {

    switch($display) {
        case true:
            $display = 'block';
        break;
        case false:
            $display = 'none';
        break;
    }

    switch($output_f) {
        case 'print':
            echo "<pre style='display: " . $display . ";' id='123456'>";
            print_r($array);
            echo "</pre>";
            break;
        case 'ser':
            echo serialize($array);
            break;
    }
}

//tmp function
//formating data for offers

function getOffersGlasses($id_prod) {
  $data_offers = [];

  $offers = \CCatalogSKU::getOffersList($id_prod, 0, array(), array('ID', 'NAME'), array());

  foreach($offers as $id_goods => $arr_values) {
    foreach($arr_values as $id_offer => $several_props) {
      //$element_ = \CIBlockElement::GetById($several_props['ID']);

      //may be this need mapping

      //get properties for each offers
      $prop = \CIBlockElement::GetPropertyValues(
          IBLOCK_MODIFICATION,
          array("ID" => $several_props['ID']),
          false,
          array("ID" => array(101, 102, 106, 107, 118))
      );

      $prop_offer_ext = $prop->Fetch();

      $catalog_quantity = \GetCatalogProduct($several_props['ID']);

      //this tmp - add we use call to Highloadblock - for get info from References - 'color' and 'material'
      //color
      $hl_color = new \WorkerHLBlock(HLBL_COLOR);
      $hl_material = new \WorkerHLBlock(HLBL_MATERIAL);
        
      $select_1 = array('UF_NAME', 'ID', 'UF_FILE', 'UF_XML_ID');
      $select_4 = array('UF_NAME', 'ID', 'UF_XML_ID', 'UF_PICTURE');
      $extractor_color = $hl_color->selectData($select_1);
      $extractor_material = $hl_material->selectData($select_4);

      $hlbl = [];

      while($item = $extractor_color->Fetch()) {
        if($item['UF_NAME'] == $prop_offer_ext['106']) {
          $hlbl['color'] = ['file' => $item['UF_FILE'], 'name' => $item['UF_NAME']];
          break;
        }
      }

      while($item = $extractor_material->Fetch()) {
        if($item['UF_NAME'] == $prop_offer_ext['107']) {
          $hlbl['material'] = ['file' => $item['UF_PICTURE'], 'name' => $item['UF_NAME']];
          break;
        }
      }

      $data_offers[$several_props['ID']] = [
        'model' => $several_props['NAME'],
        'price' => \FormatCurrency($prop_offer_ext['102'], "RUB"),
        'quantity' => $catalog_quantity['QUANTITY'],
        'brand' => $prop_offer_ext['101'],
        'color' => $hlbl['color']['name'],
        'color_f' => \CFile::GetPath($hlbl['color']['file']),
        'material' => $hlbl['material']['name'],
        'material_f' => \CFile::GetPath($hlbl['material']['file']),
        'stores' => $prop_offer_ext['118'],
      ];

    }
    
    return $data_offers;
    //this we can get price use Bitrix API
    //$price = \CPrice::GetByID();
  }
}

//this import specification
// - for goods sync - remain
//
//remoteRemainsController
//
class DataArchitector {
    private $apiKey = 'ec4cb9ccd89fa5544b9b0a7e4bbb19b6';//'c23871eaebd59650a263f23e2b0f3a71';
    private $keyIM = '236bb329-b54c-441b-86a7-46650cdbf30c';////'f9e3812c-9324-4b63-8d21-2dd4b96db071';//
    protected $mainUrlApi = 'https://optima4.itigris.ru/';
    private $apiName = 'piteroptika';//'demo';//
    private $dataCollector;
    public $dataGrid;
    public $sectionGoods;

    private $consum_gr;
    private $gender;
    private $store;
    private $period_life;
    private $_products;
    private $_errors;

    private $_tradeOffers;

    private $_tradeOfferPropertyMap;

    private $_productPropertyMap;

    private $_affectedElementsInfo;

    private $priceOff;
    private $amountOff;

    private $categoryGoods;

    //private $hl_exemplar;
    private $brand_name;


    private function getConsumGrIdByName( $name )
    {
      return $this->consum_gr[$name];
    }

    public function __construct() {
        $this->dataCollector = [];
        $this->dataGrid = [];
        $this->brand_name = [];
        $this->_products = array();
        $this->_tradeOffers = array();
        $this->_tradeOfferPropertyMap = array();
        $this->_productPropertyMap = array();
        $this->_errors = array();

        $this->loadTradeOfferPropertyMap();
        $this->loadProductPropertyMap();

        $this->_affectedElementsInfo = array();

        $this->consum_gr = [
            'Мужская' => 74,
            'Женская' => 75,
            'Унисекс' => 76,
            'Детство' => 77,
        ];

        $this->categoryGoods = [
            'glasses' => 65,
            'sunglasses' => 66,
            'accessories' => 68,
            'glasses_kids' => 69,
            'contactlenses' => 70,
        ];

        $this->consum_gr_goods = [
          'Мужская' => 17,
          'Женская' => 18,
          'Унисекс' => 23,
          'Детство' => 24,
        ];

        $this->gender = [
            'Мужская' => 71,
            'Женская' => 72,
            'Унисекс' => 73,
        ];

        $this->store = [
            '1000000014' => 5539,
            '1000000017' => 5548,
            '1000000010' => 5069,
            '1000000008' => 5055,
            '1000000007' => 5067,
            '1000000009' => 5056,
            '1000000013' => 5114,
            '1000000005' => 5070,
            '1000000006' => 5068,
        ];

        $this->period_life = [
        	'1 день' => 77,
        	'2 недели' => 78,
        	'1 месяц' => 79,
        ];

        //this correspont to section 
        $this->sectionGoods = [
            'glasses' => '25',
            'sunglasses' => '22',
            'lenses' => '23',
            'contactlenses' => '19',
            'accessories' => '35',
            'glasses_kids' => '67',
        ];

        $hl_exemplar = new \WorkerHLBlock(HLBL_BRAND);
        
        $select = array('UF_NAME', 'ID', 'UF_XML_ID');

        $extractor = $hl_exemplar->selectData($select);
        while($item = $extractor->Fetch()) {
            $this->brand_name[$item['UF_XML_ID']] = [
                'name' => $item['UF_NAME'],
                'id' => $item['ID'],
                'xml_id' => $item['UF_XML_ID']
            ];
        }
    }

    private function loadProductPropertyMap()
    {
      $rs = \CIBlockProperty::GetList(
        array('ID' => 'ASC'),
        array(
          'IBLOCK_ID' => IBLOCK_GOODS,
          'IBLOCK_TYPE' => '1c_catalog'
        )
      );

      if( !($isHaveEntries = $rs->SelectedRowsCount()) )
        goto ret;

      while( ($property = $rs->Fetch()) !== false )
        $this->_productPropertyMap[ $property['CODE'] ] = $property['ID'];

ret:
      return $isHaveEntries;
    }

    private function loadTradeOfferPropertyMap()
    {
      $rs = \CIBlockProperty::GetList(
        array('ID' => 'ASC'),
        array(
          'IBLOCK_ID' => IBLOCK_MODIFICATION,
          'IBLOCK_TYPE' => '1c_catalog'
        )
      );

      if( !($isHaveEntries = $rs->SelectedRowsCount()) )
        goto ret;

      while( ($property = $rs->Fetch()) !== false )
        $this->_tradeOfferPropertyMap[ $property['CODE'] ] = $property['ID'];

ret:
      return $isHaveEntries;
    }

    //product -
    //:accessories
    //:contactlenses
    //:glasses
    //:lenses
    //:sunglasses
    // page
    public function listData($product, $departmentId = '', $page = '') {

        $url = $this->mainUrlApi . $this->apiName . '/remoteRemains/list' . '?key=' . $this->keyIM . '&product=' . $product;

        if($departmentId) {
            $url .= '&departmentId=' . $departmentId;
        }

        if($page) {
            $url .= '&page=' . $page;
        }
        
        $array_out = json_decode(shell_exec('curl "' . $url . '"'), true);

        return $array_out;
    }

    //now with tree implementations for category
    //:contactlenses - МКЛ
    //:glasses - оправы
    //:sunglasses - солнцезащинеые очки
    //
    //return getData from REST
    public function dataSorter($sCategory) {
        switch($sCategory) {
            case 'contactlenses':
            $this->looper('contactlenses');
            break;
            case 'sunglasses':
            $this->looper('sunglasses');
            break;
            case 'glasses':
            $this->looper('glasses');
            break;
        }

        return $this->dataCollector;
    }

    //brand contactlinses
    public function contactLensesDetecter($name) {
        $brand_var = [
            '1-day',
            'acuvue',
            'air optix',
            'dailies',
            'freshlook',
            'sofciear',
        ];
        foreach($brand_var as $parse) {
            if(preg_match('/' . $parse . '/', strtolower($name))) {
                $parts = explode(' ', $parse);
                $br = '';
                if(is_array($parts)) {
                    foreach ($parts as $word) {
                        if($word == '1-day') {
                            $word = strtoupper($word);
                        }
                        $br .= (ucfirst($word) . ' ');
                    }
                }

                break;
            }
        }
        return trim($br);
    }

    //this we recount goods - we compose all the offers but places in difference stores
    public function countOfferCreate($sCategory) {
        $buffer = [];
        foreach($this->dataGrid as $key => $ar) {
            $amounter = [];
            foreach ($ar as $point => $val) {
                //trim(explode(' ', $val['model'])[0])
                if($sCategory == 'sunglasses' || $sCategory == 'glasses') {
                    $keys_unic = md5(trim($val['brand']) . trim($val['design']) . trim($val['purpose']) . trim($val['color']) . trim($val['material']));
                }
                if($sCategory == 'contactlenses') {
                    $keys_unic = md5(trim($val['name']) . trim($val['inPack']) . trim($val['color']) .  trim($val['radius']) . trim($val['diameter']) . trim($val['dioptre']) . trim($val['cylinder']) . trim($val['add']));
                }

                if(!isset($amounter[$keys_unic])) {
                    $amounter[$keys_unic] = [
                        'num' => $val['amount'],
                        'department' => $val['department']
                    ];
                }
                else {
                    $amounter[$keys_unic]['num'] += $val['amount'];
                    if(preg_match('/' . $val['department'] . '/', $amounter[$keys_unic]['department']) == 0) {
                        $amounter[$keys_unic]['department'] .= ',' . $val['department'];
                    }
                }
                //$val['amount'] =
                if(!isset($buffer[$key][$keys_unic])) {
                    $buffer[$key][$keys_unic] = $val;
                }
            }
            //here we set numbers count
            foreach ($buffer[$key] as $keySec => $val) {
                $val['amount'] = $amounter[$keySec]['num'];
                $val['department'] = $amounter[$keySec]['department'];
                $buffer[$key][$keySec] = $val;
            }
        }
        unset($this->dataGrid);
        $this->dataGrid = array_combine(array_keys($buffer), array_values($buffer));
        //clear buffer
        unset($buffer);
    }


    //all goods that we get from iTigris
    public function combinationData($sCategory) {
        foreach($this->dataCollector as $key => $data) {
            //we must model parse - we not use in MODEL last part delimeted by space
            $name_br_free = explode(' ', $data['model']);
            $name_goods = '';
            for($i = 0; $i < count($name_br_free); $i++) {
                if((count($name_br_free) - 1) == $i)
                    break;
                $name_goods .= $name_br_free[$i];
            }
            //antidebmes($name_goods, true);
            switch($sCategory) {
                case 'glasses':

                $dataIndication = md5(trim($data['brand']) . trim($name_goods) . trim($data['design']) . trim($data['purpose']));
                $this->dataGrid[$dataIndication][] = [
                    'brand' => $data['brand'],
                    'model' => $data['model'],
                    'design' => $data['design'],
                    'purpose' => $data['purpose'],
                    'manufacturer' => $data['manufacturer'],
                    'price' => $data['price'],
                    'amount' => $data['amount'],
                    'department' => $data['department'],
                    /// this prop type
                    'color' => $data['color'],
                    'material' => $data['material'],
                    'type' => $data['type'],
                ];
                break;
                case 'contactlenses':
                //compose combination
                $dataIndication = md5(trim($data['name']) . $this->contactLensesDetecter($data['name']) . trim($data['inPack']));

                $this->dataGrid[$dataIndication][] = [
                    'name' => $data['name'] . ' ' . $data['inPack'],
                    'axis' => $data['axis'],
                    'wearingPeriod' => $data['wearingPeriod'],
                    'inPack' => $data['inPack'],
                    'manufacturer' => $data['manufacturer'],
                    'price' => $data['price'],
                    'amount' => $data['amount'],
                    'department' => $data['department'],
                    /// this all for offers
                    'color' => $data['color'],
                    'radius' => $data['radius'],
                    'diameter' => $data['diameter'],
                    'dioptre' => $data['dioptre'],
                    'cylinder' => $data['cylinder'],
                    'add' => $data['add'],
                ];

                break;
                case 'sunglasses':
                //this inticator group for sunglasses - union by бренд + модель + дизайн + целевая группа
                $dataIndication = md5(trim($data['brand']) . trim($name_goods) . trim($data['design']) . trim($data['purpose']));

                $this->dataGrid[$dataIndication][] = [
                    'brand' => $data['brand'],
                    'model' => $data['model'],
                    'design' => $data['design'],
                    'purpose' => $data['purpose'],
                    'manufacturer' => $data['manufacturer'],
                    'price' => $data['price'],
                    'amount' => $data['amount'],
                    'department' => $data['department'],
                    'type' => $data['type'],
                    /// this tree but now not get color lisense
                    'color' => $data['color'],
                    'material' => $data['material'],
                ];
                break;
            }
        }
        //antidebmes($this->dataGrid, true);
    }


    //method for get brand id to link highload block, by name
    private function getBrandHL($name_br) {
        $partname = substr(md5($name_br), 0, 8);
        return $this->brand_name[$partname];
    }


    public function setPriceTypeOne($offerId, $price) {
        $rs = \CPrice::GetList(
          array(),
          array(
            'PRODUCT_ID' => intval($offerId)
          ),
          false,
          array('nTopCount' => 1),
          array('ID', 'PRICE', 'CATALOG_GROUP_ID')
        );

        $arFields = array(
          'PRODUCT_ID'        => intval($offerId),
          'CATALOG_GROUP_ID'  => 1,
          'PRICE'             => $price,
          'CURRENCY'          => 'RUB'
        );

        if( $rs->SelectedRowsCount() <= 0 )
          \CPrice::Add($arFields);
        else
        {
          $priceEntity = $rs->Fetch();
          $arFields['CATALOG_GROUP_ID'] = $priceEntity['CATALOG_GROUP_ID'];
          \CPrice::Update($priceEntity['ID'], $arFields);
        }

        unset($arFields);

        return $this;
    }

    public function setStoreAmount($offerId, $amount) {
      $rs = \CCatalogStoreProduct::GetList(
        array(),
        array(
          'PRODUCT_ID' => $offerId
        ),
        false,
        array('nTopCount' => 1),
        array('PRODUCT_ID')
      );

      $arStoreFields = array(
        'PRODUCT_ID'  => $offerId,
        'STORE_ID'    => 1,
        'AMOUNT'      => $amount
      );

      $arProductFields = array(
        'ID' => $offerId,
        'QUANTITY' => $amount
      );

      if( $rs->SelectedRowsCount() > 0 )
      {
        $r = \CCatalogStoreProduct::Update($rs->Fetch()['ID'], $arStoreFields);

        if( is_bool($r) && $r === false )
        {
          // TODO: write about this
          // echo 'NOT UPDATED STORE AMOUNT'.PHP_EOL;
          goto ret;
        }

        $rs = \CCatalogProduct::GetList(
          array(),
          array(
            'ID' => $offerId
          ),
          false,
          array('nTopCount' => 1),
          array('ID')
        );

        $isUpdated = \CCatalogProduct::Update($offerId, $arProductFields);

        if( !$isUpdated )
        {
          // TODO: write about
          // echo 'NOT UPDATED STORE AMOUNT'.PHP_EOL;
          goto ret;
        }

        goto ret;
      }

      \CCatalogStoreProduct::Add($arStoreFields);

      \CCatalogProduct::Add($arProductFields);

ret:
      return $this;
    }

    protected function searchTradeOfferByHash( $hash ) {

      foreach( $this->_tradeOffers as $tradeOffer ) {

        if( $hash == $tradeOffer['XML_ID'] && !empty($tradeOffer['PROPERTY_CML2_LINK_VALUE']) && $tradeOffer['PROPERTY_CML2_LINK_VALUE'] != 1 ) {
          file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/log_update_' . date('Ymd', time()) . '.log', 'find offer - ' . serialize($tradeOffer) . PHP_EOL, FILE_APPEND);
          return $tradeOffer;
        }
      }

      return null;
    }

    protected function searchProductByCode($code) {
      $product = null;

      foreach( $this->_products as $item )
        if( $code === $item['CODE'] )
        {
          $product = $item;
          break;
        }

      return $product;
    }

    protected function searchProductByName($search, $numbers_in_pack = false) {

        if(preg_match('/DAILIES TOTAL/', $search)) {
            $search = preg_replace('/ONE/', '1', $search);
        }

        if(preg_match('/DAY/', $search)) {
            $search = preg_replace('/1-DAY/', '1∙DAY', $search);
        }

        $product = null;

        foreach( $this->_products as $item )
          if( $search === $item['NAME'] )
          {
            $product = $item;
            break;
          }

        return $product;
    }
    
    /*
    this method for check Brand Catalog, if brand name is new we add new row one
    params:
    name brand,
    return:

    null - if success
    1 - if brand exists
    */
    public function brandChecker($name) {
      $name_mod = substr(md5($name), 0, 8);

      $hl_exemplar = new \WorkerHLBlock(HLBL_BRAND);
        
      $select = array('UF_NAME', 'ID', 'UF_XML_ID');

      $extractor = $hl_exemplar->selectDataSortFilt($select, array('ID' => 'ASC'), array('UF_XML_ID' => $name_mod));

      if(count($extractor->Fetch()) == 0) {
        $hl_exemplar->pushNewData(array('UF_XML_ID' => $name_mod, 'UF_EXTERNAL_CODE' => $name_mod, 'UF_SORT' => '500', 'UF_DESCRIPTION' => $name, 'UF_LINK' => 'piteroptika.ru', 'UF_NAME' => $name));
      }
      while($item = $extractor->Fetch()) {
        return 1;
      }
    }

    ///
    //this we detect suite name for product
    private function nameProductDetected($massive_offers) {
        $copy = $massive_offers;
        $name_variation = [];
        $count = [];
    
        //preg_match('/\d+/', $dword);
        
        foreach($copy as $key_index => $data) {
            $count[] = count(explode(' ', $data['model']));
        }
        //got to median name of model and remove some not usual names
        $mediana = floor(array_sum($count) / count($count));
        for($i = 0; $i < count($count); $i++) {
            if($count[$i] > $mediana || $count[$i] < $mediana) {
                unset($copy[$i]);
            }
        }
    
        foreach($copy as $key_index => $data) {
            $parts_name_model = explode(' ', $data['model']);
            
            for($i = 0; $i < count($parts_name_model); $i++) {
                if(empty($name_variation[$i])) {
                    $name_variation[$i][] = $parts_name_model[$i];
                }
                else {
                    if(!in_array($parts_name_model[$i], $name_variation[$i])) {
                        $name_variation[$i][] = $parts_name_model[$i];
                    }
                }
            }
        }
    
        $name = '';
        foreach($name_variation as $key => $arr) {
            if(count($arr) == 1) {
                if($name == '') {
                    $name .= $arr[0];
                }
                else {
                    $name .= (' ' . $arr[0]);
                }
            }
        }
        return $name;
    }


    private function loadProducts( $nCategoryId )
    {
      $rs = \CIBlockElement::GetList(
        array(),
        array(
          'IBLOCK_ID' => IBLOCK_GOODS,
          'IBLOCK_SECTION_ID' => $nCategoryId
        ),
        false,
        false,
        array('ID', 'NAME', 'CODE', 'UF_XML_ID')
      );

      $arProducts = array();

      while( ($arProduct = $rs->Fetch()) )
        array_push($arProducts, $arProduct);

      $this->_products = $arProducts;

      return $rs->SelectedRowsCount();
    }


    private function loadTradeOffers( $nCategoryId = null )
    {
      $rs = \CIBlockElement::GetList(
        array(),
        array(
          'IBLOCK_ID' => IBLOCK_MODIFICATION,
          'IBLOCK_TYPE' => '1c_catalog'
        ),
        false,
        false,
        array('ID', 'NAME', 'XML_ID', 'PROPERTY_CML2_LINK')
      );

      $arTradeOffers = array();

      while( ($arTradeOffer = $rs->Fetch()) )
        array_push($arTradeOffers, $arTradeOffer);

      $this->_tradeOffers = $arTradeOffers;

      return $rs->SelectedRowsCount();
    }


    private function getCategoryIdByName( $sCategory )
    {
      return $this->sectionGoods[$sCategory];
    }

    private function getCategoryIB( $sCategory )
    {
      return $this->categoryGoods[$sCategory];
    }

    private function toBitrixCode( $str, $sourceLang = 'ru', $options = array() )
    {
      $defaultOptions = array(
        'max_len' => 150,
        'replace_space' => '_',
        'change_case' => 'L'
      );
      return \CUtil::translit($str, $sourceLang, array_merge($defaultOptions, $options));
    }

    private function notEmpty( $value )
    {
      return !empty($value);
    }

    private function normalizeNameAttributes( $attributes )
    {
      return array_map(trim, array_filter($attributes, [$this, 'notEmpty']));
    }

    private function generateTradeOfferName($sCategory, $arTradeOfferData)
    {
      if( $sCategory == 'sunglasses' || $sCategory == 'glasses' ) {
          $sName = implode(' ',
              [trim(preg_replace('/\\s+/', ' ', $arTradeOfferData['brand'])), trim(preg_replace('/\\s+/', ' ', $arTradeOfferData['model']))]
          );
      } else {
          $sName = trim($arTradeOfferData['name']);
      }

      return $sName;
    }

    //private
    public function generateTradeOfferHash( $sCategory, $sTradeOfferName, $arTradeOfferData )
    {
      // generate the old trade offer hash with 6 symbols
      if( $sCategory === 'sunglasses' || $sCategory === 'glasses' )
        $attributes = array($arTradeOfferData['color'], $arTradeOfferData['material']);
      else
        $attributes = array(
          $arTradeOfferData['cylinder'],
          $arTradeOfferData['dioptre'],
          $arTradeOfferData['color'],
          $arTradeOfferData['diameter'],
          $arTradeOfferData['radius'],
          $arTradeOfferData['add']
        );

      $sOldHash = substr(md5(implode(' ', $attributes)), 0, 6);

      return substr(md5($sOldHash.$sTradeOfferName), 0, 15);
    }

    private function normalizeTradeOfferData( $data )
    {
      if( $data['brand'] === '-' )
        $data['brand'] = 'no brand';
      return $data;
    }

    private function createProductAttributes( $sCategory, $sTradeOfferName, $data )
    {
      $data = $this->normalizeTradeOfferData($data);

      $attributes = array(
        'MODIFIED_BY' => 45,
        'IBLOCK_ID' => IBLOCK_GOODS,
        'ACTIVE' => 'Y',
        'IBLOCK_SECTION_ID' => $this->getCategoryIdByName($sCategory)
      );

      //for KIDS we set special section
      if( $this->consum_gr_goods[$data['purpose']] == 24 ) {
          $attributes['IBLOCK_SECTION_ID'] = $this->getCategoryIdByName('glasses_kids');
      }

            
      $attributes['NAME'] = $sTradeOfferName;
      $attributes['CODE'] = $this->toBitrixCode($sTradeOfferName);


      if( $sCategory == 'sunglasses' || $sCategory == 'glasses' ) {
        //$properties['BRAND_REF'] = array('VALUE' => $this->getBrandHL($data['brand']));
        $attributes['PROPERTIES_VALUES']['61'] = array('VALUE' => $this->getBrandHL($data['brand']));
      } else if( $sCategory == 'contactlenses' ) {
        //$properties['BRAND_REF'] = array('VALUE' => $this->getBrandHL( $this->contactLensesDetecter($data['name']) )['xml_id']);
        $attributes['PROPERTIES_VALUES']['61'] = array('VALUE' => $this->getBrandHL( $this->contactLensesDetecter( $data['name'] ) )['xml_id']);
      }

      //$attributes = remapArrayValues($this->_productProperyMap, $properties);

      $attributes['PROPERTIES_VALUES'][64] = $this->consum_gr_goods[$data['purpose']];

      return $attributes;
    }

    private function createTradeOfferAttributes( $sCategory, $sTradeOfferName, $data, $element_id )
    {
      $nCategoryId = $this->getCategoryIB($sCategory);
      if($data['purpose'] == 'Детство') {
          $nCategoryId = 69;
      }

      $attributes = array();
      $properties = array();

      switch( $sCategory )
      {
        case 'glasses':
          $properties = remapArrayValues(
            $this->_tradeOfferPropertyMap,
            array(
                'CATEGORY'        => $nCategoryId,
                'BRAND'           => array('VALUE' => $this->getBrandHL($data['brand'])['xml_id']),
                'PRICE'           => $data['price'],
                'MODEL'           => $data['model'],
                'COLOR'           => $data['color'],
                'MATERIAL'        => $data['material'],
                'CONSUMER_GROUP'  => $this->consum_gr[$data['purpose']],
                'CML2_LINK'       => $element_id,
                'DESIGN'          => $data['design'],
                'DEPARTAMENT'     => $data['department'],
                'SEX'             => $this->gender[$data['purpose']],
                'TYPE_GL_FRAME'   => $data['type']
            )
          );
          break;

        case 'sunglasses':
          $properties = remapArrayValues(
              $this->_tradeOfferPropertyMap,
              array(
                 'CATEGORY'        => $nCategoryId,
                 'BRAND'           => array('VALUE' => $this->getBrandHL($data['brand'])['xml_id']),
                 'PRICE'           => $data['price'],
                 'MODEL'           => $data['model'],
                 'COLOR'           => $data['color'],
                 'MATERIAL'        => $data['material'],
                 'CONSUMER_GROUP'  => $this->consum_gr[$data['purpose']],
                 'CML2_LINK'       => $element_id,
                 'DESIGN'          => $data['design'],
                 'DEPARTAMENT'     => $data['department'],
                 'SEX'             => $this->gender[$data['purpose']],
                 'TYPE_GL_FRAME'   => $data['type']
              )
          );
          break;

        case 'contactlenses':
          $properties = remapArrayValues(
              $this->_tradeOfferPropertyMap,
              array(
                  'CATEGORY'        => $nCategoryId,
                  'CML2_LINK'       => $element_id,
                  'LENSES_COLOR'    => $data['color'],
                  'LENSES_SPH'      => $data['dioptre'],
                  'LENSES_BC'       => $data['radius'],
                  'LENSES_DIA'      => $data['diameter'],
                  'LENSES_CYL'      => $data['cylinder'],
                  'COUNT_IN_PACK'   => $data['inPack'],
                  'PRICE'           => $data['price'],
                  'BRAND'           => array('VALUE' => $this->getBrandHL($this->contactLensesDetecter($data['name']))['xml_id']),
                  'DEPARTAMENT'     => $data['department'],
                  'LENSES_AXIS'     => $data['axis'],
                  'WEARING_PERIOD'  => $this->period_life[$data['wearingPeriod']],
                  'MANUFACTURER'    => $data['manufacturer'] ? 'no manufact' : $data['manufacturer']
              )
          );
          break;
      }
      //use Bitrix dt not work timestamp
      //$time_now = new \Bitrix\Main\Type\DateTime();//\DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', time()));
      $attributes = array(
        'MODIFIED_BY'       => 45, //POINT TO iTigris
        'IBLOCK_SECTION_ID' => false,
        'IBLOCK_ID'         => IBLOCK_MODIFICATION,
        'PROPERTIES_VALUES' => $properties,
        'NAME' => $sTradeOfferName,
        'ACTIVE' => 'Y',
      );

      $this->priceOff = floatval(trim($data['price']));
      $this->amountOff = intval(trim($data['amount']), 10);
      if(empty($data['price'])) {
        unset($this->priceOff);
        $attributes['ACTIVE'] = 'N';
      }
      if(empty($data['amount'])) {
        unset($this->amountOff);
        $attributes['ACTIVE'] = 'N';
      }

      return $attributes;
    }

    //this we add goods into infoblock
    public function iblockWorker($sCategory) {

        $nCategoryId = $this->getCategoryIdByName($sCategory);

        $this->loadProducts($nCategoryId);
        $this->loadTradeOffers();

        $c = 0;
        //logging start
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/log_update_' . date('Ymd', time()) . '.log', ">>>>>>" . PHP_EOL, FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/log_update_' . date('Ymd', time()) . '.log', $sCategory . ' ' . date('Ymd H.i', time()) . PHP_EOL, FILE_APPEND);
        foreach( $this->dataGrid as $arTradeOfferBundle )
        {
          //unset($nProductId);

          $arTradeOfferList = array_values($arTradeOfferBundle);
          
          $nTradeOfferListCount = count($arTradeOfferList);
          $nTradeOfferListIterator = 0;

          // get the first trade offer for generate product as container if its needs

          $arTradeOfferData = $arTradeOfferList[ $nTradeOfferListIterator ];
          $sTradeOfferName = $this->generateTradeOfferName($sCategory, $arTradeOfferData);
          $sTradeOfferHash = $this->generateTradeOfferHash($sCategory, $sTradeOfferName, $arTradeOfferData);
          $arTradeOfferProductSurrogate = $this->searchTradeOfferByHash($sTradeOfferHash);

          unset($data_ext);

          if( is_null($arTradeOfferProductSurrogate) )
          {
              $arProductAttributes = $this->createProductAttributes($sCategory, $sTradeOfferName, $arTradeOfferData);
              $element = new \CIBlockElement();
              $nProductId = $element->Add($arProductAttributes);
              file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/log_update_' . date('Ymd', time()) . '.log', '1. create | ' . $nProductId . ' | ' . serialize($arTradeOfferList[0]) . PHP_EOL, FILE_APPEND);
          }
          else {
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/log_update_' . date('Ymd', time()) . '.log', '1. update | ' . $arTradeOfferProductSurrogate['PROPERTY_CML2_LINK_VALUE'] . PHP_EOL, FILE_APPEND);
            $nProductId = $arTradeOfferProductSurrogate['PROPERTY_CML2_LINK_VALUE'];
          }

          // handle all trade offer for current product 
          while( $nTradeOfferListIterator < $nTradeOfferListCount )
          {
            $arTradeOfferData = $arTradeOfferList[ $nTradeOfferListIterator++ ];

            $sTradeOfferName = $this->generateTradeOfferName($sCategory, $arTradeOfferData);

            $sTradeOfferHash = $this->generateTradeOfferHash($sCategory, $sTradeOfferName, $arTradeOfferData);

            $arTradeOffer = $this->searchTradeOfferByHash($sTradeOfferHash);

            $arTradeOfferAttributes = $this->createTradeOfferAttributes($sCategory, $sTradeOfferName, $arTradeOfferData, $nProductId );

            if( is_null($arTradeOffer) )
            {
                $arTradeOfferAttributes['XML_ID'] = $sTradeOfferHash;
                $id_off = $this->createTradeOffer($arTradeOfferAttributes);
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/log_update_' . date('Ymd', time()) . '.log', '2. ' . $id_off . ' - ' . $arTradeOfferAttributes['PROPERTIES_VALUES'][124] . ' | ' . $sTradeOfferHash . ' create ' . ' ' . serialize($arTradeOfferAttributes) . PHP_EOL, FILE_APPEND);
            }
            else
            {
                $arTradeOfferAttributes['XML_ID'] = $sTradeOfferHash;
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/log_update_' . date('Ymd', time()) . '.log', '3. ' . $sTradeOfferHash . ' update ' . serialize($arTradeOfferAttributes) . PHP_EOL, FILE_APPEND);
                $this->updateTradeOffer($arTradeOffer['ID'], $arTradeOfferAttributes);
            }
          }
        }
    }

    public function getErrors() {
        return $this->_errors;
    }
  
    public function getAffectedElementsInfo() {
        return $this->_affectedElementsInfo;
    }

    private function searchAffectedProductById( $id ) {
        foreach( $this->_affectedElementsInfo as $element ) {
            if( $element['ID'] === $id ) {
                return $element;
            }
        }
        return null;
    }

    private function searchOfferInAffectedProduct( $product, $offerId ) {
        foreach( $product['TRADE_OFFERS'] as $offer ) {
            if( $offer['ID'] === $offerId ) {
                return $offer;
            }
        }
          
        return null;
    }

    private function updateTradeOffer($nId, $attributes) {
        \CIBlockElement::SetPropertyValuesEx($nId, IBLOCK_MODIFICATION, $attributes['PROPERTIES_VALUES'], array());
        //this fix some strange error with link to goods
        $prop[124] = $attributes['PROPERTIES_VALUES'][124];
        \CIBlockElement::SetPropertyValuesEx($nId, IBLOCK_MODIFICATION, $prop, array());

        //this we correct error with time change mark
        $t = new \Bitrix\Main\Type\DateTime();
        $e = new \CIBlockElement();
        $ar = array(
            'TIMESTAMP_X' => $t,
        );
        $e->Update($nId, $ar);

        //  this we apply price
        $this->setPriceTypeOne($nId, $this->priceOff);
        //  this we apply amount
        $this->setStoreAmount($nId, $this->amountOff);
         
        return $this;
    }


    private function createTradeOffer($attributes) {

        $element = new \CIBlockElement();
        $nId = $element->Add($attributes);
        //this fix some strange error with link to goods
        //$prop[124] = $attributes['PROPERTIES_VALUES'][124];

        //this need becase some reason contact lenses and sunglasses not applayed parameters afer add
        \CIBlockElement::SetPropertyValuesEx($nId, IBLOCK_MODIFICATION, $attributes['PROPERTIES_VALUES'], array());
        //  this we apply price
        $this->setPriceTypeOne($nId, $this->priceOff);
        //  this we apply amount
        $this->setStoreAmount($nId, $this->amountOff);

        return $nId;
    }

    //without departament
    protected function looper($sCategory) {
        $page_n = 1;
        $flag = true;
        while($flag) {
            //1000000007

            $dataTmp = $this->listData($sCategory, '', $page_n);
            //$flag = false;
            if(count($dataTmp) == 1000) {
                $flag = true;
            }
            else {
                $flag = false;
            }
            $page_n++;
            $this->dataCollector = array_merge($this->dataCollector, $dataTmp);
            unset($dataTmp);
        }
    }
}

class BonusProgram {

    private $apiKey = 'c23871eaebd59650a263f23e2b0f3a71';
    private $apiName = 'demo';
    protected $mainUrlApi = 'https://optima4.itigris.ru/demo/';

    public function __construct() {

    }

    public function urlConstructor() {

    }

    //discount by card
    public function apiClientCardInfo($clientCardId) {
        return json_decode(shell_exec('curl "' . $this->mainUrlApi . $this->apiName . '/remoteClientCard/apiClientCardInfo' . '?key=' . $this->apiKey . '&clientCardId=' . $clientCardId . '"'), true);
    }

    //amount bonus on card clients
    public function apiBonusInfo($clientCardId, $withExpired) {
        switch($withExpired) {
            case true:
            $url =  $this->mainUrlApi . 'apiBonusInfo' . '?key=' . $this->apiKey . '&clientCardId=' . $clientCardId . '&withExpired=true';
            break;
            case false:
            $url =  $this->mainUrlApi . 'apiBonusInfo' . '?key=' . $this->apiKey . '&clientCardId=' . $clientCardId;
            break;
        }
        return json_decode(shell_exec('curl "' . $url . '"'), true);
    }

    // create client entity
    // return - clientId
    public function getClient($phoneNumber, $surName = '', $firstName = '', $secondName = '') {
        $url = $this->mainUrlApi . $this->apiName . '/remoteClientCard/getClient' . '?key=' . $this->apiKey . '&tel=' . $phoneNumber;
        if($surName) {
            $url .= ('&family_name=' . $surName);
        }
        if($firstName) {
            $url .= ('&first_name=' . $firstName);
        }
        if($secondName) {
            $url .= ('&patronymic_name=' . $secondName);
        }

        return json_decode(shell_exec('curl "' . $url . '"'), true);
    }

    //get number of clinet active card
    //return - registerClient
    public function getClientCard($clientId) {
        return json_decode(shell_exec('curl "' . $this->mainUrlApi . $this->apiName . '/remoteClientCard/getClientCard' . '?key=' . $this->apiKey . '&clientId=' . $clientId . '"'), true);
    }
    //registration peson in system
    public function registerClient($family_name, $first_name, $patronymic_name, $tel1, $gender, $extend_params = array()) {
        $url = $this->mainUrlApi . $this->apiName . '/remoteClientCard/registerClient' . '?key=' . $this->apiKey . '&family_name=' . $family_name . '&first_name=' . $first_name . '&patronymic_name=' . $tel1 . '&gender=' . $gender;

        foreach($extend_params as $key => $val) {
            if ($val) {
                $params[$key] = $val;
            }
        }

        $url .= http_build_query($params);
        return json_decode(shell_exec('curl "' . $url . '"'), true);
    }
    //register card - this not use departament and brand, useless info
    //$id - indicator card
    public function registerClientCard($id, $clientId) {
        return json_decode(shell_exec('curl "' . $this->mainUrlApi . $this->apiName . '/remoteClientCard/registerClientCard' . '?key=' . $this->apiKey . '&id=' . $id . '&clientId=' . $clientId . '"'), true);
    }

    //method for change tel of client
    //$tel - new telephone nummber
    public function changeClientTel($clientId, $tel) {
        return json_decode(shell_exec('curl "' . $this->mainUrlApi . $this->apiName . '/remoteClientCard/changeClientTel' . '?key=' . $this->apiKey . '&tel=' . $tel . '&clientId=' . $clientId . '"'), true);
    }

    // -- remoteBonusController -- \\

    // return: description, sum, operationDate
    public function history($clientCardId, $add_filter = array()) {

        $url = $this->mainUrlApi . $this->apiName . '/remoteBonus/history' . '?key=' . $this->apiKey . '&tel=' . $tel . '&clientId=' . $clientId;

        foreach($addFilter as $key => $val) {
            if ($val) {
                $params[$key] = $val;
            }
        }

        $url .= http_build_query($params);

        return json_decode(shell_exec('curl "' . $url . '"'), true);
    }
}

class Doctors {

    public function __construct() {

    }
    // -- remoteServicesTypesController -- \\

    public function categories($serviceTypeId) {
        return json_decode(shell_exec('curl "' . $this->mainUrlApi . $this->apiName . '/remoteServicesTypes/categories' . '?key=' . $this->apiKey . '&tel=' . $tel . '&serviceTypeId=' . $serviceTypeId . '"'), true);
    }
    //return - id, name, price, categories
    public function listData($sCategory = '') {

    }

    //other methods Yaroslav realization

}
