<?php
$arUrlRewrite = array(
	array(
		"CONDITION" => "#^/cat/([-0-9]+)/(.*)#",
		"RULE" => "section=\$1",
		"ID" => "",
		"PATH" => "/cat/section.php",
	),
	array(
		"CONDITION" => "#^/brands/([-0-9]+)/([-0-9]+)/([-0-9]+)/([-0-9]+)/(.*)#",
		"RULE" => "id=\$1&col=\$2&pid=\$3&oid=\$4",
		"ID" => "",
		"PATH" => "/brands/offer.php",
	),
	array(
		"CONDITION" => "#^/brands/([-0-9]+)/([-0-9]+)/([-0-9]+)/(.*)#",
		"RULE" => "id=\$1&col=\$2&pid=\$3",
		"ID" => "",
		"PATH" => "/brands/product.php",
	),
	array(
		"CONDITION" => "#^/brands/([-0-9]+)/([-0-9]+)/(.*)#",
		"RULE" => "id=\$1&col=\$2",
		"ID" => "",
		"PATH" => "/brands/collection.php",
	),
	array(
		"CONDITION" => "#^/brands/([-0-9]+)/(.*)#",
		"RULE" => "id=\$1",
		"ID" => "",
		"PATH" => "/brands/brand.php",
	),
	array(
		"CONDITION" => "#^/reports/realization/([-0-9]+)/(.*)#",
		"RULE" => "id=\$1",
		"ID" => "",
		"PATH" => "/reports/realization/detail.php",
	),
	array(
		"CONDITION" => "#^/reports/collections/([-0-9]+)/(.*)#",
		"RULE" => "id=\$1",
		"ID" => "",
		"PATH" => "/reports/collections/detail.php",
	),
	array(
		"CONDITION" => "#^/fin/([-0-9]+)/([-0-9]+)/(.*)#",
		"RULE" => "id=\$1&pid=\$2",
		"ID" => "",
		"PATH" => "/fin/product.php",
	),
	array(
		"CONDITION" => "#^/fin/([-0-9]+)/(.*)#",
		"RULE" => "id=\$1",
		"ID" => "",
		"PATH" => "/fin/collection.php",
	),
	array(
		"CONDITION" => "#^/discount/([-0-9]+)/(.*)#",
		"RULE" => "id=\$1",
		"ID" => "",
		"PATH" => "/discount/detail.php",
	),
);
