<?
require('prolog.php');

// Обработчики событий
\Local\System\Handlers::addEventHandlers();

// Модули битрикса
\Bitrix\Main\Loader::IncludeModule('iblock');
\Bitrix\Main\Loader::IncludeModule('highloadblock');
