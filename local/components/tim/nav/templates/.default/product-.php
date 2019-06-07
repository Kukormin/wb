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
        $discountF = $product['DISCOUNT'];

    ?>
    <h2>История изменения цен и скидок</h2>
    <table class="fix price_history">
    <colgroup width="200">
    <colgroup width="80">
        <col span="6">
    </colgroup>
	<colgroup width="100">
    <thead>
    <tr>
        <th>Дата</th>
        <th>Цена</th>
        <th>Скидка</th>
        <th>Промо</th>
        <th>Общий промо</th>
        <th>СПП</th>
        <th>Общий СПП</th>
        <th>Цена итог</th>
    </tr>
    </thead><?

	function dateCmp($a, $b)
	{
		return $a['UF_DATE'] < $b['UF_DATE'] ? -1 : 1;
	}

	$hist = \Local\Main\PriceHistory::getByProduct($product['ID']);
	$spp = \Local\Main\Spp::getByBrand($product['BRAND']);
	$merge = array_merge($hist, $spp);
	uasort($merge, 'dateCmp');

	$resPred = [
		'DATE' => null,
		'PRICE' => $product['START_PRICE'],
		'DISCOUNT' => 0,
		'PROMO' => 0,
		'ALL_PROMO' => 0,
		'SPP' => 0,
		'ALL_SPP' => 0,
	];

	$reverse = [];
	$d = 0;
	$p = 0;
	$pAll = 0;
	$pRes = 0;
	foreach ($merge as $item)
	{
		$res = $resPred;
		$res['DATE'] = $item['UF_DATE'];

		$res['PRICE_CH'] = (bool)$item['UF_PRICE_CHANGE'];
		if ($item['UF_PRICE_CHANGE'])
			$res['PRICE'] = $item['UF_PRICE'];

		$res['DISCOUNT_CH'] = (bool)$item['UF_DISCOUNT_CHANGE'];
		if ($item['UF_DISCOUNT_CHANGE'])
			$res['DISCOUNT'] = $item['UF_DISCOUNT'];

		$res['PROMO_CH'] = false;
		$res['ALL_PROMO_CH'] = false;
		if ($item['UF_PROMO_CHANGE'])
		{
			if ($item['UF_PRODUCT'])
			{
				$res['PROMO'] = $item['UF_PROMO'];
				$res['PROMO_CH'] = true;
			}
			else
			{
				$res['ALL_PROMO'] = $item['UF_PROMO'];
				$res['ALL_PROMO_CH'] = true;
			}
		}

		$res['SPP_CH'] = false;
		$res['ALL_SPP_CH'] = false;
		if (isset($item['UF_BRAND']))
		{
			if ($item['UF_BRAND'])
			{
				$res['SPP'] = $item['UF_VALUE'];
				$res['SPP_CH'] = true;
			}
			else
			{
				$res['ALL_SPP'] = $item['UF_VALUE'];
				$res['ALL_SPP_CH'] = true;
			}
		}

		array_unshift($reverse, $res);
		$resPred = $res;
	}



	?>
    <tbody><?

	$first = true;
    foreach ($reverse as $item)
    {
        $dateF = $item['DATE'];

        $priceF = number_format($item['PRICE'], 0, ',', ' ');
        $priceCl = $item['PRICE_CH'] ? ' class="ch"' : '';
        $discountF = number_format($item['DISCOUNT'], 0, ',', ' ');
		$discountCl = $item['DISCOUNT_CH'] ? ' class="ch"' : '';
		$promoF = number_format($item['PROMO'], 0, ',', ' ');
		$promoCl = $item['PROMO_CH'] ? ' class="ch"' : '';
		$allPromoF = number_format($item['ALL_PROMO'], 0, ',', ' ');
		$allPromoCl = $item['ALL_PROMO_CH'] ? ' class="ch"' : '';
		$sppF = number_format($item['SPP'], 0, ',', ' ');
		$sppCl = $item['SPP_CH'] ? ' class="ch"' : '';
		$allSppF = number_format($item['ALL_SPP'], 0, ',', ' ');
		$allSppCl = $item['ALL_SPP_CH'] ? ' class="ch"' : '';

		$promo = max($item['PROMO'], $item['ALL_PROMO']);
		$spp = max($item['SPP'], $item['ALL_SPP']);
		$res = $item['PRICE'] * (1 - $item['DISCOUNT'] / 100) * (1 - $promo / 100) * (1 - $spp / 100);

		$resF = number_format($res, 2, ',', ' ');

		if ($first)
		{
			$first = false;

			?>
			<tr class="summary">
				<td class="tal">Текущие значения</td>
				<td><?= $priceF ?></td>
				<td><?= $discountF ?></td>
				<td><?= $promoF ?></td>
				<td><?= $allPromoF ?></td>
				<td><?= $sppF ?></td>
				<td><?= $allSppF ?></td>
				<td class="tar"><?= $resF ?></td>
			</tr><?
		}

        ?>
        <tr>
			<td class="tal"><?= $dateF ?></td>
			<td<?= $priceCl ?>><?= $priceF ?></td>
			<td<?= $discountCl ?>><?= $discountF ?></td>
			<td<?= $promoCl ?>><?= $promoF ?></td>
			<td<?= $allPromoCl ?>><?= $allPromoF ?></td>
			<td<?= $sppCl ?>><?= $sppF ?></td>
			<td<?= $allSppCl ?>><?= $allSppF ?></td>
			<td class="tar"><?= $resF ?></td>
        </tr><?
    }

	$priceF = number_format($product['START_PRICE'], 0, ',', ' ');
    ?>
	<tr class="summary">
		<td class="tal">Начальное значение</td>
		<td><?= $priceF ?></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td class="tar"><?= $priceF ?>,00</td>
	</tr>
    </tbody>
    </table><?

?>
</div><?
