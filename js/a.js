$(document).ready(function () {
	$('.realization .offer').click(function () {
		var tr = $(this);
		var id = tr.data('id');
		var rows = $(this).siblings('.report[data-bar=' + id + ']');
		rows.toggleClass('hidden');
	});

	$('.log-table .wa').click(function () {
		var tr = $(this);
		tr.next().toggle();
	});

	$('.sales-table .sumr').click(function () {
		var tr = $(this);
		var id = tr.data('id');

		tr.siblings('.d-' + id).toggle();
	});

	$('.js-show-table').click(function () {
		var h = $(this);
		var table = h.next();
		table.toggleClass('hidden');
	});

	$('th.sort').click(function () {
		window.location = $(this).data('sort');
	});

	var priceTable = $('table.prices, table.realization');
	$('#hideOk').click(function() {
		priceTable.toggleClass('hideOk');
	});
	$('#hidePr').click(function() {
		priceTable.toggleClass('hidePr');
	});

	var monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль',
		'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
	var monthNamesShort = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля',
		'августа', 'сентября', 'октября', 'ноября', 'декабря'];
	var dayNames = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
	var dateFormat = 'dd.mm.yy';
	var options = {
		buttonImageOnly: false,
		monthNames: monthNames,
		monthNamesShort: monthNamesShort,
		dayNamesMin: dayNames,
		dateFormat: dateFormat,
		firstDay: 1
	};
	$('#from').datepicker(options);
	$('#to').datepicker(options);

	TableEdit.init();
    Supply.init();

});