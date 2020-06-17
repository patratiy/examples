<?php

#$_SERVER['DOCUMENT_ROOT'] = '/home/vyacheslav/imaginweb/piteroptic/public_html';

include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
Loader::includeModule("iblock");

class GetTradeOffers
{
    public $price = 0;
    public $offerId = 0;
    public $name = false;
    public $picture = false;

    public $offersIds = [];

    protected $productId;
    protected $iblockId;

    public function __construct()
    {
    }

    public static function setIdsConstruct($productId, $iblockId)
    {
        $instance = new self();
        $instance->setIds($productId, $iblockId);
        return $instance;
    }

    public function setIds($productId, $iblockId)
    {
        $this->productId = $productId;
        $this->iblockId = $iblockId;
    }

    public function getOffersIds()
    {
        $tradeOffers = CCatalogSKU::getOffersList($this->productId, $this->iblockId);

        $offersIds = [];

        foreach ($tradeOffers as $offers) {
            foreach ($offers as $offer) {
                $offersIds[] = $offer['ID'];
            }
        }

        return $offersIds;
    }

    public static function prepareData($products)
    {
        $data = [];

        $properties = ['PROPERTY_LENSES_SPH_VALUE' => 'СФЕРА',
            'PROPERTY_LENSES_CYL_VALUE' => 'ЦИЛИНДР',
            'PROPERTY_LENSES_AXIS_VALUE' => 'ОСЬ',
            'PROPERTY_LENSES_BC_VALUE' => 'BC',
            'PROPERTY_LENSES_DIA_VALUE' => 'DIA'];

        foreach ($properties as $p_key => $property) {
            $data[$p_key]['data'] = [];
            $data[$p_key]['title'] = $property;
        }

        foreach ($products as $key => $filter) {

            foreach ($data as $d_key => $datum) {

                $pare = [];
                $pare['id'] = $filter['ID'];
                $pare['value'] = $filter[$d_key];

                $data[$d_key]['data'][] = $pare;
            }
        }

        foreach ($data as $key => $datum) {

            $datum['data'] = GetTradeOffers::uniqueMultiArray($datum['data'], 'value');

            foreach ($datum['data'] as $i => $el) {
                if (empty($el['value'])) {
                    unset($datum['data'][$i]);
                }
            }

            $data[$key]['data'] = $datum['data'];
        }

        return $data;
    }

    public static function uniqueMultiArray($array, $key)
    {
        $results = [];

        $add = false;
        foreach ($array as $el) {
            foreach ($results as $result) {

                if (isset($result[$key]) && $result[$key] == $el[$key]) {

                    $add = true;
                    break;
                }
            }

            if (!$add) {
                $results[] = $el;
            }
            $add = false;
        }

        return $results;
    }

    public function products()
    {
        $this->offersIds = $this->getOffersIds();

        $arSelect = array('ID', 'NAME', 'IBLOCK_CODE', 'PROPERTY_PRICE', 'PROPERTY_LENSES_SPH', 'PROPERTY_LENSES_ADD',
            'PROPERTY_LENSES_BC', 'PROPERTY_LENSES_DIA', 'PROPERTY_LENSES_AXIS', 'PROPERTY_LENSES_CYL');
        $arFilter = array("IBLOCK_ID" => IntVal(9), "ACTIVE" => "Y", 'ID' => $this->offersIds);
        $res = CIBlockElement::GetList(array('sort' => 'asc'), $arFilter, false, false, $arSelect);

        $products = [];

        while ($ob = $res->GetNextElement()) {
            $productsObj = $ob->GetFields();

            $products[] = $productsObj;
        }

        $this->price = $products[0]['PROPERTY_PRICE_VALUE'];
        $this->offerId = $products[0]['ID'];

        return $products;
    }

}
