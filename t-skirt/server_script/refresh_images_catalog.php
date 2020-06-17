<?php

//$_SERVER["DOCUMENT_ROOT"] = "/var/www/html";

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/local/lib/highloadbk.php");

CModule::IncludeModule('iblock');

$selection_iblock_elements = CIBlockElement::GetList(
    Array("ID" => "ASC"),
    Array("IBLOCK_ID" => 8, "SECTION_ID" => array("267","234"), "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE" => "Y", "CATALOG_AVAILABLE" => "Y", "PROPERTY_ACTIVE_VALUE" => "Да" ), //"SECTION_CODE" => $section_name,
    false,
    false,
    Array("*")
);

$count_changes = 0;
while($field = $selection_iblock_elements->Fetch()) {
    CIBlockElement::SetPropertyValues(
        $field['ID'],
        8,
        array(
            "252" => 142
        ),
        "252"
    );
    
    $count_changes++;
}

echo json_encode(array('will_changed' => $count_changes));

?>