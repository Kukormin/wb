<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$sectionId = $_REQUEST['section'];
$section = \Local\Main\Sections::getById($sectionId);
if (!$section)
    return;

$APPLICATION->SetTitle($section['NAME']);
$APPLICATION->AddChainItem($section['NAME'], '/cat/' . $section['ID'] . '/');

$products = \Local\Main\Products::getAll();

?>
<table class="fix">
    <colgroup width="120">
    <colgroup width="240">
    <colgroup width="120">
    <colgroup width="120">
    <colgroup width="80">
    <colgroup width="80">
    <colgroup width="200">
    <thead>
    <tr>
        <th>Бренд</th>
        <th>Название</th>
        <th>Артикул</th>
        <th>Номенклатура</th>
        <th>Цена</th>
        <th>Скидка</th>
        <th>Цена со скидкой (окр.)</th>
    </tr>
    </thead>

    <tbody><?

    foreach ($products['ITEMS'] as $product)
    {
    	if ($product['DISABLE'])
    		continue;

        if ($product['SECTION'] != $section['ID'])
            continue;

        $brand = \Local\Main\Brands::getById($product['BRAND']);

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
        $productA = \Local\Main\Products::getA($product)

        ?><tr<?= $trClass ?>>
        <td><?= $brand['NAME'] ?></td>
        <td class="tal"><?= $productA ?></td>
        <td><?= $product['CODE'] ?></td>
        <td><?= $product['XML_ID'] ?></td>
        <td class="tar"><?= $priceF ?></td>
        <td class="tar"><?= $discountF ?></td>
        <td class="tar"><?= $pdF ?></td>
        </tr><?
    }

    ?>
    </tbody>
</table>