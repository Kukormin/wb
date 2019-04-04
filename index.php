<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Главная");
?>

	<div class="column">
		<div class="l">
			<div class="widget">
				<h4>Продажи и остатки суммарно</h4>
				<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'widget.sales-graph']);?>
			</div>
			<div class="widget">
				<h4>Загруженность складов</h4>
				<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'widget.stores']);?>
			</div>
		</div>
	</div><div class="column">
		<div class="r">
			<div class="widget">
				<h4>Топ продаж по товарам за месяц<a class="fr" href="/reports/top/">Весь топ</a></h4>
				<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'widget.top-products']);?>
			</div>
			<div class="widget">
				<h4>Топ продаж по коллекциям за месяц<a class="fr" href="/reports/top/">Весь топ</a></h4>
				<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'widget.top-collections']);?>
			</div>
		</div>
	</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>