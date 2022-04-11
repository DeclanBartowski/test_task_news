<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application,
    Bitrix\Main\UI\PageNavigation,
    Bitrix\Main\Loader,
    Bitrix\Iblock\ElementTable,
    Bitrix\Main\SystemException;

Loader::includeModule('iblock');


class Simplenews extends CBitrixComponent
{

    /**
     * @override
     */
    public function onIncludeComponentLang()
    {

        parent::onIncludeComponentLang();
        $this->includeComponentLang(basename(__FILE__));
    }

    /**
     * @param $params
     * @override
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        $params = parent::onPrepareComponentParams($params);

        if (!isset($params["CACHE_TIME"])) {
            $params["CACHE_TIME"] = 86400;
        }
        $params['CACHE_GROUPS'] = ($params['CACHE_GROUPS'] == 'Y');

        return $params;
    }

    protected function extractDataFromCache()
    {
        if ($this->arParams['CACHE_TYPE'] == 'N') {
            return false;
        }

        $additional = $this->arParams;
        $additional[] = Application::getInstance()->getContext()->getRequest()->getQueryList();

        if ($this->arParams['CACHE_GROUPS']) {
            global $USER;
            $additional[] = $USER->GetGroups();
        }
        if (check_bitrix_sessid()) {
            return false;
        }

        return !($this->StartResultCache(false, $additional));
    }

    protected function putDataToCache()
    {
        $this->endResultCache();
    }

    protected function abortDataCache()
    {
        $this->AbortResultCache();
    }

    private function initResult()
    {
        if($this->arParams['IBLOCK_ID']){


        $dates = [];
        $nav = $this->initNavParams();
        $select = [
            'NAME',
            'ACTIVE_FROM',
            'PREVIEW_PICTURE',
            'PREVIEW_TEXT',
        ];
        $filter = ['=IBLOCK_ID' => $this->arParams['IBLOCK_ID'], 'ACTIVE' => 'Y','!ACTIVE_FROM'=>false];

        $arTabs = ElementTable::getList([
            'select' => ['ACTIVE_FROM'],
            'filter' => $filter,
            'group' => ['ACTIVE_FROM'],
            'order' => ['ACTIVE_FROM' => 'DESC']
        ]);
        while ($tab = $arTabs->fetch()) {
            if (!in_array(FormatDate('Y', $tab['ACTIVE_FROM']), $this->arResult['TABS'])) {
                $this->arResult['TABS'][FormatDate('Y', $tab['ACTIVE_FROM'])]['YEAR'] = FormatDate('Y',
                    $tab['ACTIVE_FROM']);
                $dates[] = FormatDate('Y', $tab['ACTIVE_FROM']);
            }
        }
        $request = Application::getInstance()->getContext()->getRequest();
        if ($request->get('year') && in_array($request->get('year'), $dates)) {
            $this->arResult['CUR_YEAR'] = $request->get('year');
        } else {
            $this->arResult['CUR_YEAR'] = $this->closestNum($dates, date('Y'));
        }

        $this->arResult['TABS'][$this->arResult['CUR_YEAR']]['CURRENT'] = 'Y';
        $date_from = sprintf('01.01.%s', $this->arResult['CUR_YEAR']);
        $date_to = sprintf('31.12.%s', $this->arResult['CUR_YEAR']);
        $filter = [
            '=IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            '>=ACTIVE_FROM' => $date_from,
            '<=ACTIVE_FROM' => $date_to
        ];
        $newsList = ElementTable::getList([
            'select' => $select,
            'filter' => $filter,
            'order' => ['ACTIVE_FROM' => 'DESC'],
            'count_total' => true,
            'limit' => $nav->getLimit(),
            'offset' => $nav->getOffset()
        ]);
        while ($arNews = $newsList->fetch()) {
            if ($arNews['PREVIEW_PICTURE']) {
                $arNews['PREVIEW_PICTURE'] = CFile::GetFileArray($arNews['PREVIEW_PICTURE']);
            }
            $this->arResult['ITEMS'][] = $arNews;
        }
        $this->arResult['COUNT'] = $newsList->getCount();
        $this->arResult['NAV'] = $nav->setRecordCount($this->arResult['COUNT']);

        }
    }

    public function executeComponent()
    {
        try {
            if (!$this->extractDataFromCache()) {
                $this->initResult();
                $this->setResultCacheKeys(array_keys($this->arResult));
                $this->putDataToCache();
            }
            if($this->arResult['COUNT'])
            $title = sprintf('Список новостей (%s шт.)', $this->arResult['COUNT']);
            global $APPLICATION;
            $APPLICATION->SetTitle($title);
            $APPLICATION->SetPageProperty('title', $title);
            $this->includeComponentTemplate();

        } catch (SystemException $e) {
            $this->abortDataCache();

            ShowError($e->getMessage());
        }
    }

    /**
     * @return PageNavigation
     */
    protected function initNavParams()
    {
        $nav = new PageNavigation("nav");
        $nav->allowAllRecords(false)
            ->setPageSize($this->arParams['NEWS_COUNT'])
            ->initFromUri();
        return $nav;
    }

    /**
     * @param array $arr
     * @param string $num
     * @return string $res
     */
    protected function closestNum($arr, $num)
    {
        $res = false;
        sort($arr);
        $less = array_filter($arr, function ($a) use ($num) {
            return $a <= $num;
        });
        $greater = array_filter($arr, function ($a) use ($num) {
            return $a > $num;
        });
        $min = end($less);
        $max = reset($greater);
        if ($min && $max) {
            if ($num == $min) {
                $res .= $min;
            } else {
                $res .= $max;
            }
        } else {
            $res .= $min ? $min : $max;
        }
        return $res;
    }
}