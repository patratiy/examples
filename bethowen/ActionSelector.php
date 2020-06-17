<?php

namespace Bethowen\Services;

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule("catalog");

define('ASPRO_ACTION', '19');

class ActionSelector {
    public $dataIconsSection;
    public $dataActions;

    public function __construct() {
        $this->dataIconsSection = [];
        $this->dataActions = [];
    }

    public function NumToWord($num) {
        $name = [
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
        //var_dump($name[$num]);
        return $name[$num];
    }

    public function getData($iblock, $category) {
		$now = \Bitrix\Main\Type\DateTime::createFromTimestamp(time());
        $select = array('IBLOCK_ID' => $iblock, 'ACTIVE' => 'Y', '>=DATE_ACTIVE_TO' => $now);
        if(!empty($category)) {
            $select['SECTION_CODE'] = $category;
        }

        $data = \CIBlockElement::GetList(
            array('SORT' => 'ASC'),
            $select,
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_REDIRECT', 'DETAIL_PICTURE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO')
        );

        //print_r($data);

        while($select_data = $data->Fetch()) {
            //print_r($select_data);
            $date_from = \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($select_data['DATE_ACTIVE_FROM']));
            $date_to = \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($select_data['DATE_ACTIVE_TO']));

            $from = explode('-', $date_from->format('Y-m-d'));
            $to = explode('-', $date_to->format('Y-m-d'));

            //print_r($from);
            //print_r($to);

            if($from[1] == $to[1]) {
                $outstr = 'c ' . $from[2] . ' по ' . $to[2] . ' ' . $this->NumToWord($to[1]) . ' ' . $to[0];
            } else {
                $outstr = 'c ' . $from[2] . ' ' . $this->NumToWord($from[1]) . ' по ' . $to[2] . ' ' . $this->NumToWord($to[1]) . ' ' . $to[0];
            }

            //var_dump($select_data['DATE_ACTIVE_FROM'] . ' ' . $select_data['DATE_ACTIVE_TO']);
            $this->dataActions[] = [
                'image' => \CFile::GetPath($select_data['DETAIL_PICTURE']),
                'date_period' => $outstr,
                'url' => $select_data['PROPERTY_REDIRECT_VALUE'],
                'id' => $select_data['ID'],
                'link' => $select_data['PROPERTY_REDIRECT_VALUE'],
            ];
        }
    }
    //this select data from section
    public function StaticData($iblock) {
        $data = \CIBlockSection::GetList(
            array("SORT"=>"ASC"),
            array('IBLOCK_ID' => $iblock),
            false,
            array('NAME', 'UF_ICO_SVG_NOACT', 'UF_ICO_SVG_ACT', 'CODE', 'ID'),
            false
        );

        while($select = $data->Fetch()) {
            //print_r($select);
            $this->dataIconsSection[] = [
                'name' => $select['NAME'],
                'code' => $select['CODE'],
                'ico_active' => \CFile::GetPath($select['UF_ICO_SVG_ACT']),
                'ico_noact' => \CFile::GetPath($select['UF_ICO_SVG_NOACT']),
            ];
        }
    }
}


