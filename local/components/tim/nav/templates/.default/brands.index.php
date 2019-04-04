<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$brands = \Local\Main\Brands::getAll();

?>
<ul class="brands"><?

	foreach ($brands['ITEMS'] as $brand)
	{
		?>
		<li><a href="/brands/<?= $brand['ID'] ?>/""><img src="<?= $brand['PIC'] ?>" title="<?= $brand['NAME'] ?>" /></a></li><?
	}

	?>
</ul><?