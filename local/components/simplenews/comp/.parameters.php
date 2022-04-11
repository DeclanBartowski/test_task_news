<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentParameters = array(
	"PARAMETERS" => array(
		"IBLOCK_ID" => Array(
			"NAME" => Loc::getMessage('INFOBLOCK_ID'),
			"TYPE" => "STRING",
			"PARENT" => "BASE"
		),
		"NEWS_COUNT" => Array(
			"NAME" => Loc::getMessage('NEWS_COUNT'),
			"TYPE" => "STRING",
			"PARENT" => "BASE",
            "DEFAULT" => 20
		),
        "CACHE_TIME" => array(),

	)
);


?>