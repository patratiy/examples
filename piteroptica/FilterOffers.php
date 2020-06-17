<?php

define('IB_MODIFICATION', '9');

require_once dirname(__FILE__) . '/GetTradeOffers.php';

class FilterOffers extends GetTradeOffers
{
    public $ids;
    public $params;
    public $products;

    public function __construct($ids, $params = null)
    {
        $this->ids = $ids;
        if ($params) {
            $this->params = json_decode($params, 1);
        }
    }

    public function filteredProducts($selectedVal = null, $key = null)
    {
        $this->offersIds = json_decode($this->ids, 1);

        if($selectedVal && $key) {
            $key = str_replace('_VALUE', '', $key);
            $property = [$key => $selectedVal];
        }

        $arSelect = array('ID', 'NAME', 'IBLOCK_CODE', 'PROPERTY_PRICE', 'PROPERTY_LENSES_SPH', 'PROPERTY_LENSES_ADD',
            'PROPERTY_LENSES_BC', 'PROPERTY_LENSES_DIA', 'PROPERTY_LENSES_AXIS', 'PROPERTY_LENSES_CYL');
        $arFilter = array("IBLOCK_ID" => IB_MODIFICATION, "ACTIVE" => "Y", 'ID' => $this->offersIds, $property);
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

    public function run($products)
    {
        $this->products = $products;

        foreach ($this->params as $param) {
            foreach ($this->products as $prodKey => $product) {
                if ($prodKey == $param['key'] && $param['val']) {

                    foreach ($product['data'] as $dataKey => $datum) {

                        if ($datum['value'] == $param['val']) {
                            $this->products[$prodKey]['data'][$dataKey]['selected'] = 'selected';
                            $this->products[$prodKey]['isSelected'] = 'true';
                            break;
                        }
                    }
                }
            }
        }

        return $this->products;
    }
}