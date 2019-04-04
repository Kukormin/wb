<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var CMain $APPLICATION */
/** @var int $ID */

$discount = [];
if (!empty($_REQUEST['id']))
{
	$discount = \Local\Main\Discount::getById($_REQUEST['id']);
	if ($discount)
	{
		$ID = $discount['ID'];

		$APPLICATION->AddChainItem($discount['NAME']);
		$APPLICATION->SetTitle($discount['NAME']);
	}
}

if (isset($_REQUEST['save']))
{
	if ($ID)
		\Local\Main\Discount::update($ID, $_REQUEST['name'], $_REQUEST['from'], $_REQUEST['to'], $_REQUEST['value']);
	else
		\Local\Main\Discount::add($_REQUEST['name'], $_REQUEST['from'], $_REQUEST['to'], $_REQUEST['value']);

	LocalRedirect('/discount/');
}

?>
<form class="default" method="post">
	<input type="hidden" name="id" value="<?= $ID ?>">
	<p>
		<label for="name">Название:</label>
		<input id="name" name="name" type="text" value="<?= $discount['NAME'] ?>" required />
	</p>
	<p>
		<label for="from">От:</label>
		<input type="text" id="from" name="from" value="<?= $discount['FROM'] ?>" required />
	</p>
	<p>
		<label for="to">До:</label>
		<input type="text" id="to" name="to" value="<?= $discount['TO'] ?>" required />
	</p>
	<p>
		<label for="value">Значение, %:</label>
		<input id="value" name="value" type="text" value="<?= $discount['CODE'] ?>" required />
	</p>
	<p>
		<input type="submit" value="Сохранить" name="save" />
	</p>
</form>
