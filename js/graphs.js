$(document).ready(function () {

	parent = $('#svg');
	if (!parent.length)
		return;

	var shortMonths = ["янв", "фев", "мар", "апр", "май", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"];
	var dow = ["вс", "пн", "вт", "ср", "чт", "пт", "сб"];
	var rModal = $('#rect-modal');
	rModal.on('click', 'span', modalClose);
	$(document).mousedown(checkModalClose);
	$('input[name=view]').click(viewClick);
	$('input[name=store]').click(storeClick);

	var svg = d3.select("svg");
	svg.attr("width", parent.width());

	var marginBig = {top: 20, right: 45, bottom: 130, left: 45},
		marginBot = {top: height - 90, right: 45, bottom: 30, left: 45},
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

	// Шкалы масштабирования (для преобразования значений в координаты)
	var xBig = d3.scaleTime().range([0, width]).domain([dateMin, dateMax]),
		yBig = d3.scaleLinear().range([heightBig, 0]).domain([priceMin, priceMax]).nice(),
		yBigCounts = d3.scaleLinear().range([heightBig, 0]).domain([cntMin, cntMax]).nice(),
		yBigStocks = d3.scaleLinear().range([heightBig, 0]).domain([0, stocksMax]).nice(),

		xBot = d3.scaleTime().range([0, width]).domain(xBig.domain()),
		yBot = d3.scaleLinear().range([heightBot, 0]).domain(yBig.domain());

	// Оси координат
	var xAxisBig = d3.axisBottom(xBig).tickFormat(multiFormat),
		yAxisBig = d3.axisRight(yBig),
		yAxisBigCounts = d3.axisRight(yBigCounts),
		yAxisBigStocks = d3.axisLeft(yBigStocks),
		xAxisBot = d3.axisBottom(xBot).tickFormat(multiFormat);

	if (priceMax > 1000)
		yAxisBig.tickFormat(formatSum);
	if (cntMax > 1000)
		yAxisBigCounts.tickFormat(formatSum);
	if (stocksMax > 1000)
		yAxisBigStocks.tickFormat(formatSum);

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
		.attr("class", "axis")
		.call(yAxisBigStocks);
	big.append("g")
		.attr("class", "axis sum")
		.attr("transform", "translate(" + width + ",0)")
		.call(yAxisBig);
	big.append("g")
		.attr("class", "axis cnt")
		.attr("transform", "translate(" + width + ",0)")
		.call(yAxisBigCounts);

	// Ширина столбика
	var day1 = xBig(new Date(86400000)) - xBig(new Date(0));

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
		.attr("transform", function(d) { return "translate(" + xBig(d.d) + ",0)"; });

	bar.append("rect")
		.attr("class", function(d) { var w = d.d.getDay(); return w === 0 || w === 6 ? 'bg w' : 'bg'; })
		.attr("x", 0)
		.attr("y", 0)
		.attr("width", day1)
		.attr("height", heightBig);

	bar.append("rect")
		.attr("class", "sales sum")
		.attr("x", day1 * 0.2)
		.attr("y", function(d) { return yBig(d.sum); })
		.attr("width", day1 * 0.7)
		.attr("height", function(d) { return yBig(0) - yBig(d.sum); })
		.on("click", rectClick);

	bar.append("rect")
		.attr("class", "sales cnt")
		.attr("x", day1 * 0.2)
		.attr("y", function(d) { return yBigCounts(d.cnt); })
		.attr("width", day1 * 0.7)
		.attr("height", function(d) { return yBigCounts(0) - yBigCounts(d.cnt); })
		.on("click", rectClick);

	bar.append("rect")
		.attr("class", "return sum")
		.attr("x", day1 * 0.1)
		.attr("y", function() { return yBig(0); })
		.attr("width", day1 * 0.8)
		.attr("height", function(d) { return yBig(0) - yBig(d.rsum); })
		.on("click", rectClick);

	bar.append("rect")
		.attr("class", "return cnt")
		.attr("x", day1 * 0.1)
		.attr("y", function() { return yBigCounts(0); })
		.attr("width", day1 * 0.8)
		.attr("height", function(d) { return yBigCounts(0) - yBigCounts(d['rcnt']); })
		.on("click", rectClick);

	bar.append("rect")
		.attr("class", "order sum")
		.attr("x", day1 * 0.1)
		.attr("y", function(d) { return yBig(d['osum']); })
		.attr("width", day1 * 0.7)
		.attr("height", function(d) { return yBig(0) - yBig(d['osum']); })
		.on("click", rectClick);

	bar.append("rect")
		.attr("class", "order cnt")
		.attr("x", day1 * 0.1)
		.attr("y", function(d) { return yBigCounts(d['ocnt']); })
		.attr("width", day1 * 0.7)
		.attr("height", function(d) { return yBigCounts(0) - yBigCounts(d['ocnt']); })
		.on("click", rectClick);

	main.append("path")
		.attr("class", "line")
		.attr("d", function() { return line(S); });

	// Добавляем данные на маленький график
	bot.append("g")
		.attr("class", "bot")
		.selectAll("rect")
		.data(D)
		.enter().append("rect")
		.attr("x", function(d) { return xBot(d.d); })
		.attr("y", function(d) { return yBot(d.sum); })
		.attr("width", day1 * 0.7)
		.attr("height", function(d) { return yBot(-d.rsum) - yBot(d.sum); })
		.on("click", rectClick);

	// Добавляем группу на маленький график, которая будет показывать отображаемую область большого графика
	var from = 0;
	if (dateMin !== dateFrom)
		from = xBig(dateFrom);
	bot.append("g")
		.attr("class", "brush")
		.call(brush)
		.call(brush.move, [from, width]);


	function repaint() {
		var day1 = xBig(new Date(86400000)) - xBig(new Date(0));
		main.selectAll(".bar")
			.attr("transform", function(d) { return "translate(" + xBig(d.d) + ",0)"; });
		main.selectAll(".bg")
			.attr("width", day1);
		main.selectAll(".sales")
			.attr("x", day1 * 0.2)
			.attr("width", day1 * 0.7);
		main.selectAll(".return")
			.attr("x", day1 * 0.1)
			.attr("width", day1 * 0.8);
		main.selectAll(".order")
			.attr("x", day1 * 0.1)
			.attr("width", day1 * 0.7);

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

		var tm = new Date(d.d.getTime() + 86400000);
		var date = dow[d.d.getDay()] + ' ' + d.d.getDate() + ' ' + shortMonths[d.d.getMonth()] + ' ' + d.d.getFullYear();

		var html = '<span>×</span><h4>' + date + '</h4><dl>';
		html += '<dt>Заказы:</dt><dd><span>' + d['ocnt'] + '</span>' + accounting.formatNumber(d['osum'], 0, ' ', ',') + ' ₽</dd>';
		html += '<dt>Возвраты:</dt><dd><span>' + d['rcnt'] + '</span>' + accounting.formatNumber(d['rsum'], 0, ' ', ',') + ' ₽</dd>';
		html += '<dt>Продажи:</dt><dd><span>' + d.cnt + '</span>' + accounting.formatNumber(d.sum, 0, ' ', ',') + ' ₽</dd>';
		html += '</dl>';
		html += '<h4>Остатки</h4><dl>';
		for (var i in S) {
			if (S.hasOwnProperty(i)) {
				var item = S[i];
				if (item.d > d.d && item.d < tm) {
					date = item.d.getHours() + ':' + item.d.getMinutes();
					html += '<dt>' + date + '</dt><dd><span>' + item.sum + '</span>&nbsp;</dd>';
				}
			}
		}
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

	function viewClick() {
		var input = $(this);
		var view = input.val();
		parent.attr('class', 'view-' + view);
	}

	function storeClick() {
		var input = $(this);
		var form = input.closest('form');
		form.submit();
	}

});