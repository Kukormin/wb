<!doctype html>
<html lang="ru">
<head><?

	/** @var CMain $APPLICATION */
	/** @var CUser $USER */

	$showBxPanel = 0;

	?>
	<title><?$APPLICATION->ShowTitle()?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="shortcut icon" href="/i/favicon.png" type="image/x-icon" /><?

	$assetInstance = \Bitrix\Main\Page\Asset::getInstance();
	$assetInstance->addCss('/css/jquery-ui.min.css', true);
	$assetInstance->addCss('/css/a.css', true);

	$assetInstance->addJs('/js/jquery-3.2.1.min.js');
	$assetInstance->addJs('/js/jquery-ui.min.js');
	$assetInstance->addJs('/js/accounting.min.js');
	$assetInstance->addJs('/js/a.js');
	$assetInstance->addJs('/js/supply.js');
	$assetInstance->addJs('/js/table-edit.js');
	$assetInstance->addJs('/js/graphs.js');

	if ($showBxPanel)
		$APPLICATION->ShowHead();
	else
	{
		$bx = 'var bxSession={mess:{},Expand:function(){}};';
		?><script type="text/javascript"><?= $bx ?></script><?

		$APPLICATION->ShowCSS();
		$APPLICATION->ShowHeadScripts();
	}
	
	?>
</head>
<body><?

if ($showBxPanel)
	$APPLICATION->ShowPanel();

//
// Верхняя панель
//
?>
<div id="menu">
	<div><?

	?>
    <a href="/">Главная</a><?

	if (\Local\System\User::isAdmin())
	{
		?>
		<a href="/import/">Импорты</a>
		<a href="/fin/">Финансы</a>
		<a href="/discount/">Скидки</a>
		<a href="/bitrix/admin/" class="fr">Админка</a><?
	}

	if (\Local\System\User::isLogged())
	{
		?>
		<a href="/cat/">Категории</a>
		<a href="/brands/">Бренды</a>
		<a href="/supply/">Сформировать поставку</a>
		<a href="/reports/">Отчеты</a>
		<div class="header-search">
			<form action="/search/" method="get">
				<input type="text" name="q" /><button type="submit">🔎</button>
			</form>
		</div><?
	}

	?>
	</div>
</div><?

echo '<div id="body">';

if (!\Local\System\User::isLogged())
{
	$APPLICATION->IncludeComponent('bitrix:system.auth.form', '');

	die();
}

$APPLICATION->IncludeComponent('bitrix:breadcrumb', '');

if ($APPLICATION->GetCurDir() != '/')
{
    ?>
    <h1 class="page-title"><? $APPLICATION->ShowTitle(false, false) ?></h1><?
}
