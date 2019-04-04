<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<div class="img-r container"><?

    //
    // Характеристики
    //

	$brand = \Local\Main\Brands::getById($product['BRAND']);
    ?>
    <h2>Характеристики</h2>
    <dl class="props props-product">
	<dt>Бренд:</dt>
	<dd><?= $brand['NAME'] ?></dd>
    <dt>Активность:</dt>
    <dd><?= $product['ACTIVE'] ? 'да' : '<span class="warning">выключена</span>' ?></dd><?

	if (!$product['ACTIVE'] && $product['DISABLE'])
	{
		?>
		<dt>Левый товар:</dt>
		<dd><span class="warning">да</span></dd><?
	}
	?>
    <dt>Номенклатура:</dt>
    <dd><?= $product['XML_ID'] ?></dd>
    <dt>Артикул поставщика:</dt>
    <dd><?= $product['CODE'] ?></dd>
    <dt>Артикул ИМТ:</dt>
    <dd><?= $product['ARTICLE_IMT'] ?></dd>
	<dt>Артикул Цвета:</dt>
	<dd><?= $product['ARTICLE_COLOR'] ?></dd>
    <dt>Товар в админке:</dt>
    <dd><a href="<?= \Local\Main\Products::getAdminHref($product) ?>"><?= $product['ID'] ?></a></dd>
    <dt>Товар на wildberries.ru:</dt>
    <dd><a href="<?= \Local\Main\Products::getWBHref($product) ?>">Ссылка</a></dd>
    <dt>Категория:</dt>
    <dd><?

        $section = \Local\Main\Sections::getById($product['SECTION']);
        if ($section)
        {
            ?><a href="/cat/<?= $section['ID'] ?>/"><?= $section['NAME'] ?></a><?
        }
        else
        {
            ?>(нет)<?
        }

        ?>
    </dd>
    </dl><?

    //
    // Торговые предложения
    //
    ?>
    <h2>Торговые предложения</h2>

    <table class="fix">
    <colgroup width="250">
    <colgroup width="80">
    <colgroup width="200">
    <colgroup width="130">
    <colgroup width="130">
    <colgroup width="130">
    <thead>
    <tr>
        <th>Название</th>
        <th>Размер</th>
        <th>Штрихкод</th>
        <th>Артикул</th>
        <th>Себестоимость</th>
        <th>Оптовая цена</th>
    </tr>
    </thead>

    <tbody><?

    $offers = \Local\Main\Offers::getByProduct($product['ID']);
    foreach ($offers['ITEMS'] as $offer)
    {
		$offerA = \Local\Main\Offers::getA($offer, false, $product, $collection);

		?>
        <tr>
        <td class="tal"><?= $offerA ?></td>
        <td><?= $offer['SIZE'] ?></td>
        <td><?= $offer['BAR'] ?></td>
        <td><?= $offer['ARTICLE'] ?></td>
        <td><?= $offer['COST'] ?></td>
        <td><?= $offer['PRICE'] ?></td>
        </tr><?
    }

    ?>
    </tbody>
    </table><?

    //
    // Цены
    //
    $discountF = '';
    $priceF = number_format($product['PRICE'], 0, ',', ' ');
    if ($product['DISCOUNT'])
    {
        $discountF = $product['DISCOUNT'];
        $pdF = number_format(floor($product['PRICE'] * (1 - $product['DISCOUNT'] / 100)), 2, ',', ' ');
    } else
        $pdF = $priceF;

    ?>
    <h2>История изменения цен</h2>
    <table class="fix">
    <colgroup width="200">
    <colgroup width="80">
        <col span="2">
    </colgroup>
	<colgroup width="130">
    <thead>
    <tr>
        <th>Дата</th>
        <th>Цена</th>
        <th>Скидка</th>
        <th>Цена со скидкой</th>
    </tr>
    </thead>

    <tbody>
    <tr>
        <td class="tal">Текущее значение</td>
        <td class="tar"><?= $priceF ?></td>
        <td class="tar"><?= $discountF ?></td>
        <td class="tar"><?= $pdF ?></td>
    </tr><?

    $hist = \Local\Main\PriceHistory::getByProduct($product['ID']);
	$histR = [];

	$price = $product['START_PRICE'];
	$d = 0;
	foreach ($hist as $item)
	{
		if ($item['UF_PRICE_CHANGE'])
			$price = $item['UF_PRICE'];

		if ($item['UF_DISCOUNT_CHANGE'])
			$d = $item['UF_DISCOUNT'];

		$item['RES'] = $price * (1 - $d / 100);
		array_unshift($histR, $item);
	}

    foreach ($histR as $item)
    {
        $dateF = $item['UF_DATE'];

        $priceF = '';
        if ($item['UF_PRICE_CHANGE'])
            $priceF = number_format($item['UF_PRICE'], 0, ',', ' ');

        $discountF = '';
        if ($item['UF_DISCOUNT_CHANGE'])
            $discountF = number_format($item['UF_DISCOUNT'], 0, ',', ' ');

		$pdF = number_format($item['RES'], 2, ',', ' ');

        ?>
        <tr>
        <td class="tal"><?= $dateF ?></td>
        <td class="tar"><?= $priceF ?></td>
        <td class="tar"><?= $discountF ?></td>
        <td class="tar"><?= $pdF ?></td>
        </tr><?
    }

	$priceF = number_format($product['START_PRICE'], 0, ',', ' ');
    ?>
	<tr>
		<td class="tal">Начальное значение</td>
		<td class="tar"><?= $priceF ?></td>
		<td class="tar"></td>
		<td class="tar"><?= $priceF ?>,00</td>
	</tr>
    </tbody>
    </table><?

?>
</div><?
