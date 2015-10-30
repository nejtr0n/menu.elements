<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["ID"] = intval($arParams["ID"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["LIMIT"] = (intval($arParams["LIMIT"]) > 0) ? intval($arParams["LIMIT"]) : 10;
$arParams["EXTRA"] = (empty($arParams["EXTRA"]) || !is_array($arParams["EXTRA"])) ? array() : $arParams["EXTRA"];

$arResult["ITEMS"] = array();
$arResult["ELEMENT_LINKS"] = array();

// Cache menu
if($this->StartResultCache())
{
	if(!CModule::IncludeModule("iblock")) {
		$this->AbortResultCache();
	} else {
		$arFilter = array(
			"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
			"GLOBAL_ACTIVE"=>"Y",
			"IBLOCK_ACTIVE"=>"Y",
			"ACTIVE" => "Y",
			"IBLOCK_SECTION_ID" => (!empty($arParams["ID"])) ? $arParams["ID"] : false, // Subsection filter
		);
		$arOrder = array(
			"sort"=>"asc",
		);
		$arSelect = array (
			"ID",
			"NAME",
			"DETAIL_PAGE_URL"
		);
		$rsElements = CIBlockElement::GetList($arOrder, $arFilter, false, Array("nPageSize"=>50), $arSelect);

		if($arParams["IS_SEF"] !== "Y")
			$rsElements->SetUrlTemplates("", $arParams["SECTION_URL"]);
		else
			$rsElements->SetUrlTemplates($arParams["SEF_BASE_URL"].$arParams["DETAIL_PAGE_URL"], $arParams["SEF_BASE_URL"].$arParams["SECTION_PAGE_URL"]);

		while($arElement = $rsElements->GetNext()) {
			$arResult["ELEMENTS"][] = array(
				"ID" => $arElement["ID"],
				"~NAME" => $arElement["~NAME"],
				"~DETAIL_PAGE_URL" => $arElement["~DETAIL_PAGE_URL"],
			);
			$arResult["ELEMENT_LINKS"][$arElement["ID"]] = array();
		}
		$this->EndResultCache();
	}
}



// Return results
$aMenuLinksNew = array();
$menuIndex = 0;
$previousDepthLevel = 1;
foreach($arResult["ELEMENTS"] as $arElement) {
	$data = array(
		"FROM_IBLOCK" => true,
		"IS_PARENT" => false,
		"DEPTH_LEVEL" => $previousDepthLevel,
	);
	$arResult["ELEMENT_LINKS"][$arElement["ID"]][] = urldecode($arElement["~DETAIL_PAGE_URL"]);

	$aMenuLinksNew[$menuIndex++] = array(
		htmlspecialcharsbx($arElement["~NAME"]),
		$arElement["~DETAIL_PAGE_URL"],
		$arResult["ELEMENT_LINKS"][$arElement["ID"]],
		(empty($arParams["EXTRA"])) ? $data : array_merge($arParams["EXTRA"], $data),

	);
}
return $aMenuLinksNew;
