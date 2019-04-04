<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var CMain $APPLICATION */
/** @var array $arParams */

$file = __DIR__ . '/' . $arParams['PAGE'] . '.php';
if (file_exists($file))
{
    $page = trim($_REQUEST['p']);

    /** @noinspection PhpIncludeInspection */
    include($file);
}