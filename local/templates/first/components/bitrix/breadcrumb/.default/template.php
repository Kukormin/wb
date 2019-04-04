<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var CMain $APPLICATION */
/** @var array $arResult */

if (!$arResult)
    return '';

array_unshift($arResult, [
    'TITLE' => 'Главная',
    'LINK' => '/',
]);

$return = '<div class="bc">';

$cnt = count($arResult);
foreach ($arResult as $i => $item)
{
	$last = $i == $cnt - 1;
	if (!$last)
		$return .= '<a href="' . $item['LINK'] . '">' . $item['TITLE'] . '</a><span class="sep">/</span>';
	else
		$return .= '<span>' . $item['TITLE'] . '</span>';
}

$return .= '</div>';

return $return;