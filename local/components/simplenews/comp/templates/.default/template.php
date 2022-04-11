<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
$this->addExternalCss("/bitrix/css/main/bootstrap.css");

?>
<div class="news container">
    <div class="tab_list">
    <?foreach ($arResult['TABS'] as $tab){?>
    <a href="?year=<?=$tab['YEAR']?>"<?if($tab['CURRENT']=='Y')echo ' class="active"'?>><?=$tab['YEAR']?></a>
    <?}?>
    </div>
    <div class="news_list">
        <?foreach ($arResult['ITEMS'] as $arItem){
            if($arItem['PREVIEW_PICTURE']["ID"])
            $arFileTmp = CFile::ResizeImageGet($arItem['PREVIEW_PICTURE']["ID"],array("width" => 354, "height" => 194),BX_RESIZE_IMAGE_PROPORTIONAL_ALT);?>
            <div class="news_item">
                <?if($arItem['PREVIEW_PICTURE']["ID"]){?>
                <div class="img"><img src="<?=$arFileTmp['src']?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>"></div>
                <?}?>
                <div class="date"><?=FormatDate('d.m.Y',$arItem['ACTIVE_FROM'])?></div>
                <div class="name"><?=$arItem['NAME']?></div>
                <?if($arItem['PREVIEW_TEXT']){?>
                <div class="description"><?=$arItem['PREVIEW_TEXT']?></div>
                <?}?>
            </div>
        <?}?>
    </div>
    <?
    if($arResult['NAV']){
        $APPLICATION->IncludeComponent(
            "bitrix:main.pagenavigation",
            "",
            array(
                "NAV_OBJECT" => $arResult['NAV'],
                "SEF_MODE" => "N",
            ),
            false
        );
    }?>
</div>
