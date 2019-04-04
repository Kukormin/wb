<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

    ?>
    <table class="fix">
        <colgroup width="240">
        <colgroup width="120">
        <colgroup width="120">
        <colgroup width="80">
        <colgroup width="80">
        <colgroup width="200">
        <thead>
        <tr>
            <th>Название</th>
            <th>Артикул</th>
            <th>Номенклатура</th>
            <th>Цена</th>
            <th>Скидка</th>
            <th>Цена со скидкой (окр.)</th>
        </tr>
        </thead>

        <tbody><?

        $products = \Local\Main\Products::getByCollection($collection['ID']);
        $offersIds = [];

        foreach ($products['ITEMS'] as $product)
        {
        	if ($product['DISABLE'])
        		continue;

	        $discountF = '';
	        $priceF = number_format($product['PRICE'], 0, ',', ' ');
	        if ($product['DISCOUNT'])
	        {
		        $discountF = $product['DISCOUNT'];
		        $pdF = number_format(floor($product['PRICE'] * (1 - $product['DISCOUNT'] / 100)), 0, ',', ' ');
	        }
	        else
		        $pdF = $priceF;

	        $trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
	        $productA = \Local\Main\Products::getA($product, false, $collection);

            ?><tr<?= $trClass ?>>
            <td class="tal"><?= $productA ?></td>
            <td><?= $product['CODE'] ?></td>
            <td><?= $product['XML_ID'] ?></td>
            <td class="tar"><?= $priceF ?></td>
            <td class="tar"><?= $discountF ?></td>
            <td class="tar"><?= $pdF ?></td>
            </tr><?

            $offers = \Local\Main\Offers::getByProduct($product['ID']);
            foreach ($offers['ITEMS'] as $offer)
                $offersIds[] = $offer['ID'];
        }

        ?>
        </tbody>
    </table>
