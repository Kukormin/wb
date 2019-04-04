<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

ini_set('memory_limit', '2048M');

//
// Собираем продажи
//
$sales = \Local\Main\Sales::getGroupByDate();

$D = '';
$priceMax = 0;
$cntMax = 0;
foreach ($sales as $i => $item)
{
	/** @var \Bitrix\Main\Type\DateTime $date */
	$date = $item['UF_DATE'];
	$dateMax = new \Bitrix\Main\Type\DateTime($date);

	if (!$i)
	{
		$dateMin = new \Bitrix\Main\Type\DateTime($date);
		$dateMinF = $dateMin->format('c');
	}

	$date->add('1 hour');
	$dateF = $date->format('c');

	if ($item['SUM'] > $priceMax)
		$priceMax = $item['SUM'];
	if ($item['CNT'] > $cntMax)
		$cntMax = $item['CNT'];

	if ($D)
		$D .= ',';

	$D .= '{d:new Date("' . $dateF . '"),cnt:' . $item['CNT'] . ',sum:' . round($item['SUM'], 2) . ',rcnt:' . $item['RETURN_CNT'] . ',rsum:' . round($item['RETURN_SUM'], 2) . '}';
}

$D = 'D=[' . $D . '];';
$dateMax->add('1 day');

//
// Собираем остатки
//

$stocks = \Local\Main\StocksHistory::getGroupByDate();

$S = '';
$stocksMax = 0;
foreach ($stocks as $i => $item)
{
	/** @var \Bitrix\Main\Type\DateTime $date */
	$date = $item['UF_DATE'];
	$dateF = $date->format('c');

	if ($item['SUM'] > $stocksMax)
		$stocksMax = $item['SUM'];

	if ($S)
		$S .= ',';

	$S .= '{d:new Date("' . $dateF . '"),sum:' . $item['SUM'] . '}';
}

$S = 'S=[' . $S . '];';

if ($date > $dateMax)
	$dateMax = $date;
$dateMaxF = $dateMax->format('c');

?>
	<script src="/js/d3.v4.min.js"></script>
	<div id="svg">
		<svg width="1650" height="750"></svg>
	</div>
	<div id="rect-modal"></div>
	<script>

		var shortMonths = ["янв", "фев", "мар", "апр", "май", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"];
		var rModal = $('#rect-modal');
		rModal.on('click', 'span', modalClose);
		$(document).mousedown(checkModalClose);
		var D = []; <?= $D ?>
		var S = []; <?= $S ?>

		var svg = d3.select("svg");
		var marginBig = {top: 20, right: 45, bottom: 130, left: 45},
			marginBot = {top: 660, right: 45, bottom: 30, left: 45},
			width = +svg.attr("width") - marginBig.left - marginBig.right,
			heightBig = +svg.attr("height") - marginBig.top - marginBig.bottom,
			heightBot = +svg.attr("height") - marginBot.top - marginBot.bottom;

		var locale = d3.timeFormatLocale({
			"dateTime": "%A, %e %B %Y г. %X",
			"date": "%d.%m.%Y",
			"time": "%H:%M:%S",
			"periods": ["AM", "PM"],
			"days": ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"],
			"shortDays": ["вс", "пн", "вт", "ср", "чт", "пт", "сб"],
			"months": ["январь", "февраль", "март", "апрель", "май", "июнь", "июль", "август", "сентябрь", "октябрь", "ноябрь", "декабрь"],
			"shortMonths": shortMonths
		});

		var formatMillisecond = locale.format(".%L"),
			formatSecond = locale.format(":%S"),
			formatMinute = locale.format("%H:%M"),
			formatHour = locale.format("%H:%M"),
			formatDay = locale.format("%d.%m"),
			formatWeek = locale.format("%d.%m"),
			formatMonth = locale.format("%B"),
			formatYear = locale.format("%Y");

		// Шкалы масштабирования (для преобразования значений в координаты)
		var xBig = d3.scaleTime().range([0, width]),
			yBig = d3.scaleLinear().range([heightBig, 0]),
			yBigCounts = d3.scaleLinear().range([heightBig, 0]),
			yBigStocks = d3.scaleLinear().range([heightBig, 0]),

			xBot = d3.scaleTime().range([0, width]),
			yBot = d3.scaleLinear().range([heightBot, 0]);

		// Оси координат
		var xAxisBig = d3.axisBottom(xBig),
			yAxisBig = d3.axisRight(yBig).tickFormat(formatSum),
			yAxisBigCnt = d3.axisLeft(yBigStocks).tickFormat(formatSum),

			xAxisBot = d3.axisBottom(xBot);

		// Форматирование тиков
		xAxisBig.tickFormat(multiFormat);
		xAxisBot.tickFormat(multiFormat);

		// Слой на маленьком графике для отображения видимой области большого графика
		var brush = d3.brushX()
			.extent([[0, 0], [width, heightBot]]) // границы (начальные размеры области)
			.on("brush end", brushed);

		// Объект зума
		var zoom = d3.zoom()
			.scaleExtent([1, Infinity]) // границы масштаба
			.translateExtent([[0, 0], [width, heightBig]]) // границы смещения
			.extent([[0, 0], [width, heightBig]])
			.on("zoom", zoomed);

		// Область обрезания
		svg.append("defs").append("clipPath")
			.attr("id", "clip")
			.append("rect")
			.attr("width", width)
			.attr("height", heightBig);

		// Прямоугольник поверх большого графика для реализации зума
		svg.append("rect")
			.attr("class", "zoom")
			.attr("width", width)
			.attr("height", +svg.attr("height") - marginBig.bottom)
			.attr("transform", "translate(" + marginBig.left + "," + marginBig.top + ")")
			.call(zoom)
			.on("click", modalClose);

		// Группа для больших графиков
		var big = svg.append("g")
			.attr("transform", "translate(" + marginBig.left + "," + marginBig.top + ")");

		var main = big.append("g")
			.attr("class", "main");

		// Группа для маленьких графиков
		var bot = svg.append("g")
			.attr("transform", "translate(" + marginBot.left + "," + marginBot.top + ")");

		// Минимальные и максимальные значения на графиках
		xBig.domain([new Date('<?= $dateMinF ?>'), new Date('<?= $dateMaxF ?>')]);
		yBig.domain([1, <?= $priceMax ?>]).nice();
		yBigCounts.domain([1, <?= $cntMax ?>]);
		yBigStocks.domain([1, <?= $stocksMax ?>]);

		xBot.domain(xBig.domain());
		yBot.domain(yBig.domain());

		// Добавляем ось х для маленьких графиков
		bot.append("g")
			.attr("class", "axis axis--xBot")
			.attr("transform", "translate(0," + heightBot + ")")
			.call(xAxisBot);

		// Добавляем ось х для главного графика
		big.append("g")
			.attr("class", "axis axis--xMain")
			.attr("transform", "translate(0," + heightBig + ")")
			.call(xAxisBig);
		big.append("g")
			.attr("class", "axis axis--yBig")
			.call(yAxisBigCnt);
		big.append("g")
			.attr("class", "axis axis--yBigStocks")
			.attr("transform", "translate(" + width + ",0)")
			.call(yAxisBig);

		// Ширина столбика
		var day1 = xBig(new Date(79200000)) - xBig(new Date(0));

		// График остатков
		var line = d3.line()
			.x(function(d) { return xBig(d.d); })
			.y(function(d) { return yBigStocks(d.sum); })
			.curve(d3.curveLinear);

		// Добавляем данные на главный график
		var bar = main.selectAll(".bar")
			.data(D)
			.enter()
			.append("g")
			.attr("class", "bar")
			.attr("data-cnt", function(d) { return d.cnt; })
			.attr("data-sum", function(d) { return d.sum; })
			.attr("transform", function(d) { return "translate(" + xBig(d.d) + ",0)"; });

		bar.append("rect")
			.attr("class", "price")
			.attr("x", 0)
			.attr("y", function(d) { return yBig(d.sum); })
			.attr("width", day1)
			.attr("height", function(d) { return yBig(0) - yBig(d.sum); })
			.on("click", rectClick);

		bar.append("rect")
			.attr("class", "cnt")
			.attr("x", day1 / 4)
			.attr("y", function(d) { return yBigCounts(d.cnt); })
			.attr("width", day1 / 2)
			.attr("height", function(d) { return yBigCounts(0) - yBigCounts(d.cnt); })
			.on("click", rectClick);

		bar.append("rect")
			.attr("class", "rprice")
			.attr("x", 0)
			.attr("y", function(d) { return yBig(d.rsum); })
			.attr("width", day1)
			.attr("height", function(d) { return yBig(0) - yBig(d.rsum); })
			.on("click", rectClick);

		bar.append("rect")
			.attr("class", "rcnt")
			.attr("x", day1 / 4)
			.attr("y", function(d) { return yBigCounts(d['rcnt']); })
			.attr("width", day1 / 2)
			.attr("height", function(d) { return yBigCounts(0) - yBigCounts(d['rcnt']); })
			.on("click", rectClick);

		/*main.selectAll("rect")
			.data(D)
			.enter()
			.append("rect")
			.attr("data-cnt", function(d) { return d.cnt; })
			.attr("data-sum", function(d) { return d.sum; })
			.attr("x", function(d) { return xBig(d.d); })
			.attr("y", function(d) { return yBig(d.sum); })
			.attr("width", day1)
			.attr("height", function(d) { return yBig(0) - yBig(d.sum); })
			.on("click", rectClick);*/

		main.append("path")
			.attr("class", "line")
			.attr("d", function() { return line(S); });

		// Добавляем данные на маленький график
		bot.append("g")
			.attr("class", "bot")
			.selectAll("rect")
			.data(D)
			.enter().append("rect")
			.attr("data-cnt", function(d) { return d.cnt; })
			.attr("data-sum", function(d) { return d.sum; })
			.attr("x", function(d) { return xBot(d.d); })
			.attr("y", function(d) { return yBot(d.sum); })
			.attr("width", day1)
			.attr("height", function(d) { return yBot(0) - yBot(d.sum); })
			.on("click", rectClick);

		// Добавляем группу на маленький график, которая будет показывать отображаемую область большого графика
		bot.append("g")
			.attr("class", "brush")
			.call(brush)
			.call(brush.move, xBig.range());


		function repaint() {
			var day1 = xBig(new Date(79200000)) - xBig(new Date(0));
			main.selectAll(".bar")
				.attr("transform", function(d) { return "translate(" + xBig(d.d) + ",0)"; });
			main.selectAll(".price")
				.attr("width", day1);
			main.selectAll(".rprice")
				.attr("width", day1);
			main.selectAll(".cnt")
				.attr("width", day1 / 2)
				.attr("x", day1 / 4);
			main.selectAll(".rcnt")
				.attr("width", day1 / 2)
				.attr("x", day1 / 4);
			main.selectAll("path")
				.attr("d", function() { return line(S); });

			big.select(".axis--xMain").call(xAxisBig);

		}

		function brushed() {
			if (d3.event.sourceEvent && d3.event.sourceEvent.type === "zoom") return; // ignore brush-by-zoom
			var s = d3.event.selection || xBot.range();
			xBig.domain(s.map(xBot.invert, xBot));
			repaint();
			svg.select(".zoom").call(zoom.transform, d3.zoomIdentity
				.scale(width / (s[1] - s[0]))
				.translate(-s[0], 0));
		}

		function zoomed() {
			if (d3.event.sourceEvent && d3.event.sourceEvent.type === "brush") return; // ignore zoom-by-brush
			var t = d3.event.transform;
			xBig.domain(t.rescaleX(xBot).domain());
			repaint();
			bot.select(".brush").call(brush.move, xBig.range().map(t.invertX, t));
		}

		function multiFormat(date) {
			return (d3.timeSecond(date) < date ? formatMillisecond
				: d3.timeMinute(date) < date ? formatSecond
					: d3.timeHour(date) < date ? formatMinute
						: d3.timeDay(date) < date ? formatHour
							: d3.timeMonth(date) < date ? (d3.timeWeek(date) < date ? formatDay : formatWeek)
								: d3.timeYear(date) < date ? formatMonth
									: formatYear)(date);
		}

		function formatSum(sum) {
			return sum / 1000 + 'K';
		}

		function rectClick(d) {
			var rect = $(this);

			var date = d.d.getDate() + ' ' + shortMonths[d.d.getMonth()] + ' ' + d.d.getFullYear();

			var html = '<span>×</span><h4>' + date + '</h4><dl>';
			html += '<dt>Количество:</dt><dd>' + d.cnt + '</dd>';
			html += '<dt>Сумма:</dt><dd>' + accounting.formatNumber(d.sum, 0, ' ', ',') + ' ₽</dd>';
			html += '<dt>Возвраты:</dt><dd>' + d['rcnt'] + '</dd>';
			html += '<dt>Возвраты:</dt><dd>' + accounting.formatNumber(d['rsum'], 0, ' ', ',') + ' ₽</dd>';
			html += '</dl>';
			rModal.html(html);
			var w = rModal.width();
			var t = rect.position().top - rModal.height() - 10;
			var l = rect.position().left + rect.attr('width') / 2 - w / 2;
			var st = $(window).scrollTop() + 50;
			var ww = $('#body').width() - 5;
			if (t < st)
				t = st;
			if (l < 5)
				l = 5;
			if (l + w > ww)
				l = ww - w;

			rModal.css({
				top: t,
				left: l
			});
			rModal.show();
		}

		function modalClose() {
			rModal.hide();
		}

		function checkModalClose(e) {
			var target = $(e.target);
			if (!target.closest('#rect-modal').length)
				modalClose();
		}

	</script>
<?