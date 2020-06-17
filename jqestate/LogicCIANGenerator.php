<?php

#IMAGINWEB 2018-2019

//premium for all
$publishPrem = true;

ini_set('display_errors', '1');
ini_set('error_reporting', 'E_ALL');

$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";

require($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_before.php');

CModule::IncludeModule('iblock');

define('IBLOCK_OBJ', 5);
define('IBLOCK_CIAN', 10);
define('IBLOCK_VIL', 4);
define('XML_FEED', '/home/bitrix/www/service-script/feed_cian.xml');
define('SERVERIMAGE', '93.90.221.61/UPLOAD/MAIN/foto/');
//3 minutes
set_time_limit(180);

//beautify xml structure
class XMLConstructor{
    
    public $xml_doc;
    
    public function __construct($root_node) {
        $this->xml_doc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . "<" . $root_node . ">" . "\n";
        $this->xml_doc .= "\t" . '<feed_version>2</feed_version>' . "\n";
    }
    
    public function addNodeOpen($node_name, $tabulation) {
        $this->xml_doc .= $this->tabMaker($tabulation) . '<' . $node_name . '>' . "\n";
    }
    
    public function addNode($node_name, $inner, $tabulation) {
        $this->xml_doc .= $this->tabMaker($tabulation) . '<' . $node_name . '>' . $inner . '</' . $node_name . '>' . "\n";
    }
    
    public function addNodeClose($node_name, $tabulation) {
        $this->xml_doc .= $this->tabMaker($tabulation) . '</' . $node_name . '>' . "\n";
    }
    
    public function endXml($root_node) {
        $this->xml_doc .= "</" . $root_node . ">" . "\n";
    }

    public function generateXml($file_name) {
        file_put_contents($file_name, $this->xml_doc);
    }
    
    private function tabMaker($c) {
        $tab = '';
        for($i = 0; $i < $c; $i++) {
            $tab .= "\t";
        }
        return $tab;
    }
}

class LogicCIANGenerator {
    private $arrRoad;
    private $descrptionSale;
    private $descrptionRent;
    private $imagesObj;
    private $feeObj;
    private $arrOffers;
    public $exportProp;
    
    public $publish_term;
    public $type;
    public $no_img;
    
    public function __construct() {
        $this->arrRoad = [];
        $this->descrptionSale = array();
        $this->descrptionRent = array();
        $this->arrOffers = array();
        
        $this->imagesObj = array();
        $this->feeObj = array();
        $this->exportProp = array();
        
        $this->getCianIdRoad();
    }

    public function getCianIdRoad() {
        $this->arrRoad = [
            'Алтуфьевское' => 1,
            'Большое Московское кольцо' => 42,
            'Боровское' => 2,
            'Быковское' => 3,
            'Варшавское' => 4,
            'Внуковское' => 49,
            'Володарское' => 53,
            'Волоколамское' => 5,
            'Горьковское' => 6,
            'Дмитровское' => 7,
            'Домодедовское' => 129,
            'Егорьевское' => 8,
            'Ильинское' => 9,
            'Калужское' => 10,
            'Каспий' => 52,
            'Каширское' => 11,
            'Киевское' => 12,
            'Кудиновское' => 128,
            'Куркинское' => 13,
            'Ленинградское' => 14,
            'Малое Московское кольцо' => 41,
            'Машкинское' => 38,
            'Международное' => 15,
            'Минское' => 16,
            'Можайское' => 17,
            'Монинское' => 46,
            'Москва - Аэропорт Домодедово, А-105' => 39,
            'Москва-Санкт-Петербург, M-11' => 43,
            'Новокаширское' => 18,
            'Новорижское' => 19,
            'Новорязанское' => 20,
            'Новосходненское' => 21,
            'Новоугличское' => 51,
            'Носовихинское' => 22,
            'Осташковское' => 23,
            'Подушкинское' => 24,
            'Путилковское' => 40,
            'Пятницкое' => 25,
            'Рогачевское' => 26,
            'Рублево-Успенское' => 27,
            'Рублевское' => 28,
            'Рязанское' => 36,
            'Северный объезд Одинцова' => 44,
            'Сергиев Посад - Череповец' => 50,
            'Симферопольское' => 29,
            'Сколковское' => 30,
            'Фряновское' => 32,
            'Шереметьевское' => 33,
            'Щелковское' => 34,
            'Ярославское' => 35,
        ];

    }
    //>>>temp
    private function obtainExternStack() {
		$this->imagesObj = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/service-script/images_objects_25122018.json'), true);
		$this->feeObj = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/service-script/agent_fee_obj_25122018.json'), true);
    }

    //get main info from 10 infoblock
    
    public function getStatusExport() {
        $arrFilter = array(
            'IBLOCK_ID' => IBLOCK_CIAN,
            'ACTIVE' => 'Y'
        );

        $elements = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            $arrFilter,
            false,
            false,
            array(
                'ID',
                'IBLOCK_ID',
                'SORT',
                'ACTIVE',
                'NAME',
                'CODE',
                'PROPERTY_TISA_ID',
                'PROPERTY_CIAN_PUBLISH_TERM',
                'PROPERTY_OBJECT_5',
                'PROPERTY_PUBLISH_OBJ_TYPE',
                'PROPERTY_USE_BET',
                'PROPERTY_BET_RENT',
                'PROPERTY_BET_SALE',
                'PROPERTY_DESC_RENT',
                'PROPERTY_DESC_SALE',
                'PROPERTY_DEPOSIT',
                'PROPERTY_PREPAID_MON',
                'PROPERTY_SPEC_ADDRESS'
            )
        );
        
        while($data_ext = $elements->Fetch()){
            $tisaid = $data_ext['PROPERTY_TISA_ID_VALUE'];
            $prop = CIBlockElement::GetPropertyValues(
                IBLOCK_CIAN,
                array('PROPERTY_TISA_ID' => $tisaid),
                false,
                array("ID" => array('163', '165'))
            );
            
            $get_prop = $prop->Fetch();
            
            $this->exportProp[$tisaid] = [
                'publish' => $get_prop['163'],
                'type' => $get_prop['165'],
                'bet_sale' => $data_ext['PROPERTY_BET_SALE_VALUE'],
                'bet_rent' => $data_ext['PROPERTY_BET_RENT_VALUE'],
                'use_bet' => $data_ext['PROPERTY_USE_BET_VALUE'],
                'desc_rent' => $data_ext['PROPERTY_DESC_RENT_VALUE']['TEXT'],
                'desc_sale' => $data_ext['PROPERTY_DESC_SALE_VALUE']['TEXT'],
                'deposit' => $data_ext['PROPERTY_DEPOSIT_VALUE'],
                'prepay_month' => $data_ext['PROPERTY_PREPAID_MON_VALUE'],
                'address' => $data_ext['PROPERTY_SPEC_ADDRESS_VALUE']
            ];
        }

    }
    
    protected function getAllObject() {
        //get properties of export status
        $this->getStatusExport();
        $this->obtainExternStack();
        
        $arObjectId = array();
        $arFilter = Array(
            "IBLOCK_ID" => IBLOCK_OBJ,
        );
        
        $res = CIBlockElement::GetList(
            array("SORT"=>"ASC"),
            $arFilter,
            false,
            false,
            array(
                'ID',
                'NAME',
                'IBLOCK_ID',
                'ACTIVE',
                'CODE',
                'PROPERTY_OLD_SYSTEM_ID',
                'PROPERTY_DESCRIPTION_ARTICLE',
                'PROPERTY_DESCRIPTION_ARTICLE_RENT',
                'PROPERTY_OLD_SYSTEM_ID',
                'PROPERTY_SO_PRICE',
                'PROPERTY_RO_PRICE',
                'PROPERTY_TYPE_CODE',
                'PROPERTY_OBJECT_PICTURES',
                'PROPERTY_DISTRICT',
                'PROPERTY_ADDRESS_NAME',
                'PROPERTY_ROUTE_NAME',
                'PROPERTY_RO_CURRENCY',
                'PROPERTY_SO_CURRENCY',
                'PROPERTY_LOCALITY',
                'PROPERTY_MAP_COORD',
                'PROPERTY_REMOTE_MKAD',
                'PROPERTY_S_AREA',
                'PROPERTY_AREA',
                'PROPERTY_S_BEDROOMS',
                'PROPERTY_C_POWER_SUPPLY',
                'PROPERTY_C_WATER_SUPPLY',
                'PROPERTY_C_GAS_SUPPLY',
                'PROPERTY_S_FLOORS',
                'PROPERTY_BUILT_YEAR',
                'PROPERTY_S_ROOMS',
                'PROPERTY_S_WALL_MATERIAL',
                'PROPERTY_S_FURNITURE'
            )
        );
        
        while($ar_fields = $res->Fetch()) {
            $id = $ar_fields['PROPERTY_OLD_SYSTEM_ID_VALUE'];
            $arObjectId[] = $id ;

            $this->descrptionSale[$id] = $this->exportProp[$id]['desc_sale'];
            $this->descrptionRent[$id] = $this->exportProp[$id]['desc_rent'];
            

            $arr_images = $this->imagesObj[$id];
            if (empty($arr_images)) {
                //try get from bitrix
                foreach($ar_fields['PROPERTY_OBJECT_PICTURES_VALUE'] as $image) {
                    $arData = CFile::GetByID($image);
                    $arr_images[] = 'https://jqestate.com' . CFile::GetPath($image);//'https://' . SERVERIMAGE . '/' . $id . '/' . str_replace('.jpg', '.jpeg', $arData->Fetch()['ORIGINAL_NAME']);
                }
                if(!is_array($ar_fields['PROPERTY_OBJECT_PICTURES_VALUE'])) {
                    $arr_images[] = 'https://jqestate.com' . CFile::GetPath($ar_fields['PROPERTY_OBJECT_PICTURES_VALUE']);//'https://' . SERVERIMAGE . '/' . $id . '/' . str_replace('.jpg', '.jpeg', $arData->Fetch()['ORIGINAL_NAME']);
                }

            }

            //we construct this becase before this data select from XML file with DataBase
            
            $this->arrOffers[$id] = [
                "ObjectCode" => $id,
                "Price" => $ar_fields['PROPERTY_SO_PRICE_VALUE'],
                "PriceRent" => $ar_fields['PROPERTY_RO_PRICE_VALUE'],
                "ArticleTypeCode" => $ar_fields['PROPERTY_TYPE_CODE_VALUE'],
                "Images" => $arr_images,
                "District" => $ar_fields['PROPERTY_DISTRICT_VALUE'],
                "Locality" => $ar_fields['PROPERTY_LOCALITY_VALUE'],
                "AddressName" => $ar_fields['PROPERTY_ADDRESS_NAME_VALUE'],
                "RouteName" => $ar_fields['PROPERTY_ROUTE_NAME_VALUE'],
                "CurrencyRent" => $ar_fields['PROPERTY_RO_CURRENCY_VALUE'],
                "Currency" => $ar_fields['PROPERTY_SO_CURRENCY_VALUE'],
                "Article" => $id,
                "RemoteMKAD" => $ar_fields['PROPERTY_REMOTE_MKAD_VALUE'],
                "Longitude" => explode(",", $ar_fields['PROPERTY_MAP_COORD_VALUE'])[1],
                "Latitude" => explode(",", $ar_fields['PROPERTY_MAP_COORD_VALUE'])[0],
                "SpaceDesign" => $ar_fields['PROPERTY_S_AREA_VALUE'],
                "BedRooms" => $ar_fields['PROPERTY_S_BEDROOMS_VALUE'],
                "PowerSupply" => $ar_fields['PROPERTY_C_POWER_SUPPLY_VALUE'],
                "WaterSupply" => $ar_fields['PROPERTY_C_WATER_SUPPLY_VALUE'],
                "GasSupply" => $ar_fields['PROPERTY_C_GAS_SUPPLY_VALUE'],
                "Floor" => $ar_fields['PROPERTY_S_FLOORS_VALUE'],
                "BuiltYear" => $ar_fields['PROPERTY_BUILT_YEAR_VALUE'],
                "Rooms" => $ar_fields['PROPERTY_S_ROOMS_VALUE'],
                "WallMaterial" => $ar_fields['PROPERTY_S_WALL_MATERIAL_VALUE'],
                "Furniture" => $ar_fields['PROPERTY_S_FURNITURE_VALUE'],
                "LandArea" => $ar_fields['PROPERTY_AREA_VALUE'],
                "AgentFee" => $this->feeObj[$ar_fields['PROPERTY_OLD_SYSTEM_ID_VALUE']]
            ];
        }
        
    }
    
    protected function setTypeOfferObj($type, $id) {
        if($type == "sale") {
            $arrTypeOffer = array(
                'Дом' => 'houseSale',
                'Таунхаус' => 'townhouseSale',
                'Участок' => 'landSale',
                'Квартира' => 'flatSale'
            );
            // Категория объявления
            $typeOffer = $arrTypeOffer[$this->arrOffers[$id]['ArticleTypeCode']];
        }
        if($type == "rent") {
            $arrTypeOffer = array(
                'Дом' => 'houseRent',
                'Таунхаус' => 'townhouseRent',
                'Квартира' => 'flatRent',
                'Участок' => 'landRent'
            );
            // Категория объявления
            $typeOffer = $arrTypeOffer[$this->arrOffers[$id]['ArticleTypeCode']];
        }
        return $typeOffer;
    }
    
    protected function bodyXmlComposer($xml, $id, $data, $type) {
        //$publishPrem = true;
        unset($bet);
        
        //set description text
        $desc = (($type == 'sale') ? $this->descrptionSale[$id] : $this->descrptionRent[$id]);
        
        $repeat = true;
        $dop_numer = false;
        
        $typeOffer = $this->setTypeOfferObj($type, $id);

        if(!$typeOffer) goto notadd;
        
        // Исключить без картинок
        if(!$this->arrOffers[$id]['Images']) {
            $this->no_img[] = $id;
            goto notadd;
        }
        
        // Проверка обязательных полей
        if(!$this->arrOffers[$id]['Price'] && $type == "sale" ) {
            goto notadd;
        }
        if(!$this->arrOffers[$id]['PriceRent'] && $type == "rent" ) {
            goto notadd;
        }

        // Сгенирировать адрес
        if($this->arrOffers[$id]['District']) 
            $address = trim($this->arrOffers[$id]['District']) . " район, ";
        if($this->arrOffers[$id]['Locality'])
            $address .= trim($this->arrOffers[$id]['Locality']) . ", ";
        $address .= trim($this->arrOffers[$id]['AddressName']);
        
        if($data['address']) {
            $address = $data['address'];
        }

        // Найти ид дороги

        if ($this->arrOffers[$id]['RouteName'] == "Рублёво-Успенское") {
            $idRoad = $this->arrRoad[str_replace("ё", "е", $this->arrOffers[$id]['RouteName'])];
        }
        else {
            $idRoad = $this->arrRoad[$this->arrOffers[$id]['RouteName']];
        }

        // Определить тип ремонта
        /* Тип ремонта:
            cosmetic — Косметический
            design — Дизайнерский
            euro — Евроремонт
            no — Без ремонта
        */
        $arrRepair = array(
            'дизайнерский' => 'design',
            'черновая отделка' => 'no',
            'под ключ' => 'euro',
            'частично под ключ' => 'cosmetic',
            'под чистовую отделку' => 'no',
            'коробка' => 'no',
        );
        
        $repairType = $arrRepair[$this->arrOffers[$id]['Renovate']];

        // Определить материал стен
    
        /* Тип дома:
            aerocreteBlock — Газобетонный блок
            boards — Щитовой
            brick — Кирпичный
            foamConcreteBlock — Пенобетонный блок
            gasSilicateBlock — Газосиликатный блок
            monolith — Монолитный
            wireframe — Каркасный
            wood — Деревянный
        */

        $arrWall = array(
            'кирпич' => 'brick',
            'монолит' => 'monolith',
            'блюмакс' => '',
            'блоки' => 'aerocreteBlock',
            'канадская технология' => 'wireframe',
            'дерево' => 'wood',
        );

        $materialType = $arrWall[$this->arrOffers[$id]['WallMaterial']];
            
        // Определить валюту
    
        /* Валюта:
            eur — Евро
            rur — Рубль (по умолчанию)
            usd — Доллар
        */
//            
        $arrСurrency = array(
            'Рубль' => 'rur',
            'Доллар' => 'usd',
            'Евро' => 'eur'
        );
        
        $currency = $arrСurrency[$this->arrOffers[$id]['Currency']];
        $currencyRent = $arrСurrency[$this->arrOffers[$id]['CurrencyRent']];
            
            
        // для квартиры
        /*
            FlatRoomsCount Количество комнат:
            от 1 до 5 – сколькикомнатная квартира
            6 – многокомнатная квартира (более 5 комнат)
            7 – свободная планировка
            9 – студия (Int32)
        
            FloorNumber Этаж (Int64)
        
            FloorsCount Количество этажей в здании (Int64)
        
            SaleType Тип продажи:
            alternative — Альтернатива
            free — Свободная продажа
        */
        //>>> start XML root each object

        $xml->addNodeOpen('object', 1);
        $xml->addNode('Category', $typeOffer, 2);

        /**
         * Должна выбирать в зависимости от типа, учитываться при аренде
         */

        $xml->addNode('ExternalId', $this->arrOffers[$id]['ObjectCode'] . (($type == 'rent') ? '_r' : '_s'), 2);

        //here BET
        if($type == 'sale' && $data['use_bet'] == 'Да' && $data['bet_sale'] != '') {
            $bet = $data['bet_sale'];
        }
        if($type == 'rent' && $data['use_bet'] == 'Да' && $data['bet_rent'] != '') {
            $bet = $data['bet_rent'];
        }
        if(isset($bet)) {
            $xml->addNodeOpen('Auction', 2);
            $xml->addNode('Bet', $bet, 3);
            $xml->addNodeClose('Auction', 2);
        }

        $desc = str_replace('\"', '&quot;', $desc);
        $desc = str_replace('\'', '&apos;', $desc);
        $desc = str_replace('&', '&amp;', $desc);
        $desc = str_replace('>', '&gt;', $desc);
        $desc = str_replace('<', '&lt;', $desc);
        
        //Описание
        $xml->addNode('Description','ID: ' . $id . ', ' . $desc, 2);
        // Москва, 3-й Кадашёвский переулок, 7
        $xml->addNode('Address', $address, 2);
        // Координаты.
        if($this->arrOffers[$id]['Latitude'] && $this->arrOffers[$id]['Longitude']){
            $xml->addNodeOpen('Coordinates', 2);
            $xml->addNode('Latitude', $this->arrOffers[$id]['Latitude'], 3);
            $xml->addNode('Longitude', $this->arrOffers[$id]['Longitude'], 3);
            $xml->addNodeClose('Coordinates', 2);
        }

        $xml->addNodeOpen('Phones', 2);
        $xml->addNodeOpen('PhoneSchema', 3);
        
        // Код страны
        $xml->addNode('CountryCode', '+7', 4);
        // Номер без кода
        $xml->addNode('Number', '4950232478', 4);
        $xml->addNodeClose('PhoneSchema', 3);
        $xml->addNodeClose('Phones', 2);
        
        $xml->addNodeOpen('Highway', 2);
        // Ид дороги
        $xml->addNode('Id', $idRoad, 3);
        // Удаленность от мкад
        if($this->arrOffers[$id]['RemoteMKAD']) {
            $xml->addNode('Distance', $this->arrOffers[$id]['RemoteMKAD'], 3);
        }
        $xml->addNodeClose('Highway', 2);
        
        // Название коттеджного поселка (String)
        $xml->addNode('SettlementName', $this->arrOffers[$id]['AddressName'], 2);
        // Общая площадь, м² (Double)
        if($this->arrOffers[$id]['SpaceDesign']) {
            $xml->addNode('TotalArea', $this->arrOffers[$id]['SpaceDesign'], 2);
        }
        // Количество спален (Int64)
        if($this->arrOffers[$id]['BedRooms']) {
            $xml->addNode('BedroomsCount', $this->arrOffers[$id]['BedRooms'], 2);
        }
        
        $arNewImages = array();
        
        foreach ($this->arrOffers[$id]['Images'] as $image) {
            $arNewImages[] = $image;
        }
        if (!is_array($this->arrOffers[$id]['Images'])) {
            $arNewImages[] = $this->arrOffers[$id]['Images'];
        }
    
        if($arNewImages){
            $xml->addNodeOpen('Photos', 2);
            foreach ($arNewImages as $key => $value) {
                $xml->addNodeOpen('PhotoSchema', 3);
                // Сcылка на картинку
                
                $xml->addNode('FullUrl', $value, 4);
                $xml->addNode('IsDefault', 'true', 4);
                
                $xml->addNodeClose('PhotoSchema', 3);
            }
            $xml->addNodeClose('Photos', 2);
        }
        // Тип ремонта
        if($repairType) {
            $xml->addNode('RepairType', $repairType, 2);
        }
        
        if($this->arrOffers[$id]['Furniture'] == "полностью") {
            $xml->addNode('HasFurniture', "true", 2);
        }

        if($this->arrOffers[$id]['PowerSupply']) {
            $xml->addNode('HasElectricity', "true", 2);
        }
        // Есть водоснабжение (Boolean)
        if($this->arrOffers[$id]['WaterSupply']) {
            $xml->addNode('HasWater', "true", 2);
        }
        
        // Есть газ (Boolean)
        if($this->arrOffers[$id]['GasSupply']) {
            $xml->addNode('HasGas', "true", 2);
        }
        
        $xml->addNodeOpen('Building', 2);
        // Количество этажей в здании (Int64), Это этаж для квартиры
        if($this->arrOffers[$id]['Floor']) {
            $xml->addNode('FloorsCount', $this->arrOffers[$id]['Floor'], 3);
        }
        // Год постройки (Int64)
        if($this->arrOffers[$id]['BuiltYear']) {
            $xml->addNode('BuildYear', $this->arrOffers[$id]['BuiltYear'], 3);
            #$Building->addChild('BuildYear', $arOffer['BuiltYear']);
        }
        // Тип дома
        $xml->addNode('MaterialType', $materialType, 2);
        $xml->addNodeClose('Building', 2);
    
        //this we add "premium" - for type of placed in CIAN
        //see more https://www.cian.ru/help/add/premium/
        if($data['publish']) {
            $xml->addNodeOpen('PublishTerms', 2);
            $xml->addNodeOpen('Terms', 3);
            $xml->addNodeOpen('PublishTermSchema', 4);
            $xml->addNodeOpen('Services', 5);
            if(in_array(75, $data['publish'])) {
                $xml->addNode('ServicesEnum', 'premium', 6);
            }
            if(in_array(76, $data['publish'])) {
                $xml->addNode('ServicesEnum', 'top3', 6);
            }
            
            //express this we set additional - TOP3
            $xml->addNodeClose('Services', 5);
            
            
            $xml->addNodeClose('PublishTermSchema', 4);
            $xml->addNodeClose('Terms', 3);
            $xml->addNodeClose('PublishTerms', 2);
        }

//        /* Отопление:
//            autonomousGas — Автономное газовое
//            centralCoal — центральное угольное
//            centralGas — центральное газовое
//            diesel — Дизельное
//            electric — Электрическое
//            fireplace — Камин
//            no — Нет
//            solidFuelBoiler — Твердотопливный котел
//            stove — Печь
//        */
        
        if($this->arrOffers[$id]['LandArea']){
            $xml->addNodeOpen('Land', 2);
            // Площадь участка (Double)
            $xml->addNode('Area', $this->arrOffers[$id]['LandArea'], 3);
            // Единица измерения
            $xml->addNode('AreaUnitType', 'sotka', 3);
            $xml->addNodeClose('Land', 2);
        }
        
        $xml->addNodeOpen('BargainTerms', 2);

        if($type == 'sale'){
            // Цена (Double)
            $xml->addNode('Price', $this->arrOffers[$id]['Price'], 3);
            // Валюта
            $xml->addNode('Currency', $currency, 3);
        }

        if($type == 'rent') {
            // Цена (Double)
            $xml->addNode('Price', $this->arrOffers[$id]['PriceRent'], 3);
            // Валюта
            $xml->addNode('Currency', $currencyRent, 3);

            if($data['deposit'] != '') {
                $xml->addNode('PrepayMonths', $data['prepay_month'], 3);
                $xml->addNode('Deposit', $data['deposit'], 3);
                $xml->addNode('LeaseTermType', 'longTerm', 3);
            }
        }
        
        if($type == 'rent') {
            $xml->addNode('ClientFee', $this->arrOffers[$id]['AgentFee'], 3);
            $xml->addNode('AgentFee', $this->arrOffers[$id]['AgentFee'], 3);
        }
        
        if($typeOffer == "flatSale" || $typeOffer == "flatRent"){
            // Общее количество комнат в квартире.
            if($arOffer['Rooms']) $object->addChild('FlatRoomsCount', $arOffer['Rooms']);
            //  Этаж (Int64)
            if($arOffer['Floor']) $object->addChild('FloorNumber', $arOffer['Floor']);
            $object->addChild('SaleType', 'free');
        }
        $xml->addNodeClose('BargainTerms', 2);
        
        //>>> close xml tag
        $xml->addNodeClose('object', 1);
        
        // this replace by info from 10 infoblock
       notadd:
    }
    
    public function genFinalXml($xml) {

        $this->getCianIdRoad();
        
        //this store ID's obj without image - then put to log
        $this->no_img = array();
        
        $this->publish_term = [
            '72' => 'free',
            '73' => 'highlight',
            '74' => 'paid',
            '75' => 'premium',
            '76' => 'top3'
        ];
        
        $this->type = [
            '77' => 'rent',
            '78' => 'sale'
        ];
        
        //get info about export object
        $this->getAllObject();
        
        // check limit of feed size
        $count = 0;
		

        foreach ($this->exportProp as $id => $data) {
            
            $type = $this->type[$data['type'][0]];
            $this->bodyXmlComposer($xml, $id, $data, $type);
            if(isset($data['type'][1])) {
                $type = $this->type[$data['type'][1]];
                $this->bodyXmlComposer($xml, $id, $data, $type);
            }
            $count++;
            
            if($count == 100000){
                die('limit exhausted');
            }

        }
        print_r($this->no_img);
    }
}

$xml_feed = new XMLConstructor('feed');

$data = new LogicCIANGenerator();

$data->genFinalXml($xml_feed);

$xml_feed->addNodeClose('feed', 0);

$xml_feed->generateXml(XML_FEED);
