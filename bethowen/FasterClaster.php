<?php

namespace Bethowen\Helpers;

\Bitrix\Main\Loader::includeModule('iblock');

define('IBLOCK_CLICK', '61');
define('LONG_STORE_IN_DAYS', '1');

\Bitrix\Main\Loader::includeModule("iblock");

//select data - this we select url and names

class FasterClaster {
    public $memCache;
    public $name_claster;
    public $section_name;
    public $section_id;
    private $filter_ban;

    public function __construct($story_baners = null) {
        if($story_baners == null) {
            $this->memCache = new \Memcached();
        } else {
            $this->memCache = new \Memcached($story_baners);
        }
        $this->memCache->addServer('127.0.0.1', 11211);
        $this->name_claster = $story_baners;
    }

    public function setSection($sec_id) {
        if($sec_id == 3) {
            $this->$section_name = \CIBlockSection::GetByID('7')->Fetch()['NAME'];
        } else {
            $this->$section_name = \CIBlock::GetByID($sec_id)->Fetch()['NAME'];
        }
        $this->section_id = $sec_id;
        if($this->section_id == '3') {
            $filter = array('IBLOCK_ID' => $this->section_id, 'SECTION_ID' => '7', 'ACTIVE' => 'Y');
        } else {
            $filter = array('IBLOCK_ID' => $this->section_id, 'ACTIVE' => 'Y');
        }
        $this->filter_ban = $filter;
        return $this->$section_name;
    }

    public function getData($key) {
        return $this->memCache->get($key);
    }

    public function setData($key, $value) {
        $this->memCache->addByKey($this->name_claster, $key, $value, time() + (60 * 60 * 24 * LONG_STORE_IN_DAYS));
        //this we update if case key some reason exist
        if($this->memCache->getResultCode() == \Memcached::RES_NOTSTORED) {
            $this->upDateSpecKey($key, $value);
        }
    }

    public function setDataTimout($key, $value, $timeout) {
        $this->memCache->addByKey($this->name_claster, $key, $value, time() + $timeout);
        //this we update if case key some reason exist
        if($this->memCache->getResultCode() == \Memcached::RES_NOTSTORED) {
            $this->upDateSpecKeyTimeout($key, $value, $timeout);
        }
    }

    public function getKeys() {
        return $this->memCache->getAllKeys();
    }

    public function upDateSpecKey($key, $value) {
        $this->memCache->replaceByKey($this->name_claster, $key, $value, time() + (60 * 60 * 24 * LONG_STORE_IN_DAYS));
    }

    public function upDateSpecKeyTimeout($key, $value, $timeout) {
        $this->memCache->replaceByKey($this->name_claster, $key, $value, time() + $timeout);
    }

    private function getUrlFollow($id_iblock, $arr) {
        switch($id_iblock) {
            case '3':
                $url = $arr['PROPERTY_URL_STRING_VALUE'];
                break;
            case '40':
                $url = $arr['CODE'];
                break;
            case '19':
                $url = $arr['PROPERTY_REDIRECT_VALUE'];
                break;
        }
        return $url;
    }

    public function fullDataSet() {

        $select = \CIBlockElement::GetList(
            array('ID' => 'ASC'),
            $this->filter_ban,
            false,
            false,
            array('ACTIVE', 'CODE', 'NAME', 'ID', 'PROPERTY_URL_STRING', 'PROPERTY_REDIRECT' , 'SECTION_CODE', 'TIMESTAMP_X')
        );

        while($item = $select->Fetch()) {
            $url = $this->getUrlFollow($this->section_id, $item);

            $this->setData(sha1($item['ID']), [$item['ID'], $item['NAME'], $url, $item['TIMESTAMP_X']]);
            die();
        }
    }

    public function setDataNew($id_element) {
        $this->filter_ban['ID'] = $id_element;

        $select = \CIBlockElement::GetList(
            array('ID' => 'ASC'),
            $this->filter_ban,
            false,
            false,
            array('ACTIVE', 'CODE', 'NAME', 'ID', 'PROPERTY_URL_STRING', 'PROPERTY_REDIRECT', 'SECTION_CODE', 'TIMESTAMP_X')
        );

        $item = $select->Fetch();

        $url = $this->getUrlFollow($this->section_id, $item);

        $this->setData(sha1($id_element), [$item['ID'], $item['NAME'], $url, $item['TIMESTAMP_X']]);
    }

    public function topBanerCache($is_head) {
        $key = sha1('top_baner/data');
        $key_ctb = sha1('top_baner/baner-dynamic');

        $block = \CIBlock::GetList(
            array('ID' => 'ASC'),
            array('CODE' => 'top-banners-fix'),
            false
        );

        $date_now = \Bitrix\Main\Type\DateTime::createFromTimestamp(time());

        $element = \CIBlockElement::GetList(
            array('SORT' => 'ASC'),
            array('IBLOCK_ID' => $block->Fetch()['ID'], "ACTIVE" => "Y"),
            false,
            false,
            array('ID', 'DATE_ACTIVE_TO', "DATE_ACTIVE_FROM", 'PROPERTY_URL_LINK', 'PROPERTY_COLOR_BACK', 'PROPERTY_FILE_SM', 'PROPERTY_FILE_FULL', 'PROPERTY_URL_LINK', "PROPERTY_HEIGHT_CSS", "PROPERTY_HTML_BODY")
        );

        $elem = [];

        while($items = $element->Fetch()) {
            //$date_now
            $date_from = \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($items['DATE_ACTIVE_FROM']));
            $date_to = \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($items['DATE_ACTIVE_TO']));
            if($date_now > $date_from && $date_now < $date_to) {
                $elem = $items;
                break;
            }
        }

        $width_main_baner_top = \CFile::GetFileArray($elem['PROPERTY_FILE_FULL_VALUE'])['WIDTH'];
        $width_mobile_baner_top = \CFile::GetFileArray($elem['PROPERTY_FILE_SM_VALUE'])['WIDTH'];
        if(!$is_head) {
            $this->setData($key, [
                'main' => $elem,
                'size' => ['width-modile' => $width_mobile_baner_top, 'width' => $width_main_baner_top]
            ]);
        } else {
            $cookie = sha1($elem['ID'] . $elem['DATE_ACTIVE_TO'] . $elem['DATE_ACTIVE_FROM']);
            $this->setData($key_ctb, [$cookie]);
        }
    }

    public function close() {
        $this->memCache->quit();
    }

	public function __destruct() {
		$this->memCache->quit();
    }
}