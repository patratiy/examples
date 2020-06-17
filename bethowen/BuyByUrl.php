<?

namespace Bethowen\Helpers;

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule("catalog");

//IB with goods
define('GOODS_IB_ID', 26);
//IB with trade offer
define('OFFER_IB_ID', 27);

class BuyByUrl {
    public $items_to_buy;
    public $basket;
    public $items_is_set;
    public $url_par;

    public function __construct($use_sess) {
        $this->items_to_buy = array();
        $this->items_is_set = [];
    }

    protected function parseUrl($url_param) {
        $this->url_par = $url_param;
        $items_to_buy = explode(',', $_REQUEST[$url_param]);
        
        if(!empty($items_to_buy)) {
            foreach($items_to_buy as $value) {
                array_push($this->items_to_buy, strval($value));
            }
        }
        if($items_to_buy == "") {
            unset($this->items_to_buy);
        }
    }

    protected function setBasket() {
        $this->basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
    }

    protected function getElementsId() {
        if(!empty($this->items_to_buy)) {

            switch($this->url_par) {
                case "items":
                    $elements = \CIBlockElement::GetList(
                        array("SORT" => "ASC"),
                        array("IBLOCK_ID" => OFFER_IB_ID, "ACTIVE" => "Y", "PROPERTY_CML2_ARTICLE" => $this->items_to_buy),
                        false,
                        false,
                        array('CODE', 'NAME', 'ID')
                    );
                    while($item = $elements->Fetch()) {
                        $this->items_is_set[] = $item['ID'];
                    }
                    if(\CUser::IsAuthorized() == false) {
                        $key = 'data/basket/' . sha1($_SERVER['REMOTE_ADDR']);
                        $cache = new \Bethowen\Helpers\FasterClaster();
                        $cache->setDataTimout($key, $this->items_is_set, 3600);
                        $cache->close();
                    }
                    break;
                case "codes":
                    foreach($this->items_to_buy as $id_) {
                        $this->items_is_set[] = $id_;
                    }
                    if(\CUser::IsAuthorized() == false) {
                        $key = 'data/basket/' . sha1($_SERVER['REMOTE_ADDR']);
                        $cache = new \Bethowen\Helpers\FasterClaster();
                        $cache->setDataTimout($key, $this->items_is_set, 3600);
                        $cache->close();
                    }
                    break;
            }
        }
    }

    public function addToBusketUrl($url_param) {
        $this->parseUrl($url_param);
        $this->getElementsId();
        $this->setBasket();
        if(\CUser::IsAuthorized()) {
            $quantity = 1;

            foreach($this->items_is_set as $id) {
                $set_item = $this->basket->createItem('catalog', $id);
                $item = array(
                    'QUANTITY' => $quantity,
                    'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                    'LID' => \Bitrix\Main\Context::getCurrent()->getSite(),
                    'PRODUCT_PROVIDER_CLASS' => '\Bitrix\Catalog\Product\CatalogProvider',
                );

                $set_item->setFields($item);
            }
            $this->basket->save();
            LocalRedirect('/basket/');
        }
    }

    public function addToBusketSession() {
        $this->setBasket();
        
        $quantity = 1;
        $key = 'data/basket/' . sha1($_SERVER['REMOTE_ADDR']);
        
        $cache = new \Bethowen\Helpers\CacheEng();
        $data = $cache->getData($key);
        foreach($data as $id) {
            $set_item = $this->basket->createItem('catalog', $id);
            $item = array(
                'QUANTITY' => $quantity,
                'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                'LID' => \Bitrix\Main\Context::getCurrent()->getSite(),
                'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
            );
            $set_item->setFields($item);
        }
        $this->basket->save();
        $cache->deleteData($key);
        $cache->close();
    }
}