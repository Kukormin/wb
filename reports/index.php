<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Отчеты");?>

	<div class="container">
		<ul>
			<li><a href="check/">Проверка на корректность</a></li>
			<li><a href="realization/">Продажи по реализации</a></li>
			<li><a href="top/">Топ</a></li>
			<li><a href="collections/">Продажи по коллекциям</a></li>
		</ul>
	</div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>