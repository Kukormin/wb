var Supply = {
	init: function() {
		this.table = $('.supply-table');
		if (!this.table.length)
			return false;

		this.allCb = this.table.find('th .m');
		this.cbs = this.table.find('td .m');
		this.form = $('.supply-form');
		this.head = this.table.find('thead.main-head');
		this.headTop = this.head.offset().top;

		this.allCb.click(this.allCbClick);
		this.cbs.click(this.cbClick);
		$('.section-cb').click(this.sectionCbClick);
		$('.collection-cb').click(this.collectionCbClick);

		$('#hideZeroRes').click(function() {
			Supply.table.toggleClass('hideZeroRes');
		});
		$('#hideZeroUl').click(function() {
			Supply.table.toggleClass('hideZeroUl');
		});
		$('#hideZeroDef').click(function() {
			Supply.table.toggleClass('hideZeroDef');
		});
		$('#hideNotZeroTarget').click(function() {
			Supply.table.toggleClass('hideNotZeroTarget');
		});
		$('#showDefUln').click(function() {
			Supply.table.toggleClass('showDefUln');
		});
		$('#showAdd').click(function() {
			Supply.table.toggleClass('showAdd');
		});
		$('.store-cb').click(this.storeCbClick);
		$('#ignoreDeficit').click(this.ignoreDeficit);

		$(document).on('table.edit', this.changeTarget);
		$(window).scroll(this.onScroll);

		this.onScroll();
	},

	allCbClick: function() {
		var cb = $(this);
		var checked = cb.prop('checked');
		Supply.cbs.prop('checked', checked);
		Supply.setRowClass(Supply.cbs);
		Supply.updateSummary();
	},

	cbClick: function() {
		Supply.setRowClass($(this));
		Supply.checkAllCb();
		Supply.updateSummary();
	},

	updateSummary: function() {
		var deficit = 0;
		var price = 0;
		var R = 0;
		var uln = 0;
		var target = 0;
		var stocks = 0;
		var cnt = 0;
		Supply.cbs.filter(':checked:visible').each(function() {
			var cb = $(this);
			var id = cb.data('id');
			var item = DATA[id];
			deficit += item['DEFICIT'];
			price += item['PRICE'];
			R += item['R'];
			uln += item['ULN'];
			target += item['TARGET'];
			stocks += item['STOCKS'];
			cnt++;
		});

		$('.js-deficit').text(deficit);
		$('.js-price').text(Math.round(price / cnt));
		$('.js-R').text(R);
		$('.js-uln').text(uln);
		$('.js-target').text(target);
		$('.js-stocks').text(stocks);
		$('.js-cnt').text(cnt);
	},

	changeTarget: function(e, offerId, storeId, value) {
		if (DATA.hasOwnProperty(offerId)) {
			if (typeof storeId !== 'undefined') {
				DATA[offerId]['TARGET'] = parseInt(value, 10);
				Supply.updateSummary();
				Supply.calculateResult(offerId);
			}
			else {
				var R = parseInt(value, 10);
				var tr = Supply.table.find('tr[data-id=' + offerId + ']');
				var input = tr.find('input[type=hidden]');
				var td = tr.find('td[data-r]');
				var item = DATA[offerId];
				if (R > 0)
					tr.removeClass('zR');
				else
					tr.addClass('zR');

				input.val(R);
				item['R'] = R;
				td.text(R);
				td.next().text('Установлено вручную');
				td.attr('class', 'e hlm');
				td.next().attr('class', 'hlm');

				Supply.checkAllCb();
				Supply.updateSummary();
			}
		}
	},

	calculateResult: function(offerId) {
		var data = DATA[offerId];
		var R = 0;
		var textcode = 0;
		if (!data['TARGET'])
		{
			textcode = 1;
		}
		else if (data['TARGET'] <= data['STOCKS'])
		{
			textcode = 2;
		}
		else if (!data['ULN'])
		{
			textcode = 3;
		}
		else if (!data['DEFICIT'])
		{
			textcode = 4;
		}
		else
		{
			R = data['TARGET'] - data['STOCKS'];
			if (data['ULN'] < R)
			{
				R = data['ULN'];
				textcode = 11;
			}
			if (data['DEFICIT'] < R)
			{
				R = data['DEFICIT'];
				textcode = 12;
			}
			if (kind > 1 && R < monoMin)
			{
				var d = monoMin - R;
				if (d < monoCorrect && monoMin <= data['DEFICIT'] && monoMin <= data['ULN']) {
					R = monoMin;
					textcode = 41;
				}
				else
				{
					R = 0;
					textcode = 13;
				}
			}
		}

		if (kind > 1 && R)
		{
			var after = data['ULN'] - R;
			if (after > 0 && after < monoMin && R + after <= data['DEFICIT'])
			{
				R += after;
				textcode = 42;
			}
		}

		var tr = Supply.table.find('tr[data-id=' + offerId + ']');
		var input = tr.find('input[type=hidden]');
		var td = tr.find('td[data-r]');

		input.val(R);
		td.text(R);
		td.next().text(textByCode[textcode]);

		var rClass = '';
		if (textcode > 40)
			rClass = 'hlp';
		else if (textcode === 1)
			rClass = 'hl1';
		else if (textcode === 12)
			rClass = 'hl2';
		else if (textcode === 13)
			rClass = 'hl3';
		td.attr('class', 'e ' + rClass);
		td.next().attr('class', rClass);
	},

	setRowClass: function(cbs) {
		cbs.each(function() {
			var cb = $(this);
			var tr = cb.closest('tr');
			var checked = cb.prop('checked');
			if (checked)
				tr.removeClass('excl');
			else
				tr.addClass('excl');
		});
	},

	checkAllCb: function() {
		var allChecked = true;
		Supply.cbs.each(function() {
			var cb = $(this);
			var checked = cb.prop('checked');
			if (!checked) {
				allChecked = false;
				return false;
			}
		});
		Supply.allCb.prop('checked', allChecked);
	},

	sectionCbClick: function() {
		var cb = $(this);
		var checked = cb.prop('checked');
		var id = cb.val();
		var tr = Supply.table.find('tr[data-section=' + id + ']');
		if (checked)
			tr.removeClass('hidden');
		else
			tr.addClass('hidden');
	},

	collectionCbClick: function() {
		var cb = $(this);
		var checked = cb.prop('checked');
		var id = cb.val();
		var tr = Supply.table.find('tr[data-collection=' + id + ']');
		if (checked)
			tr.removeClass('hidden');
		else
			tr.addClass('hidden');
	},

	storeCbClick: function() {
		var cb = $(this);
		var checked = cb.prop('checked');
		var id = cb.val();
		var st = Supply.table.find('.st' + id);
		if (checked)
			st.addClass('sts');
		else
			st.removeClass('sts');
	},

	ignoreDeficit: function() {
		var cb = $(this);
		var checked = cb.prop('checked');
		var val = checked ? '1' : '0';
		var url = location.href;
		var p = url.indexOf('igd=');
		if (p > 0) {
			url = url.substr(0, p + 4) + val + url.substr(p + 5);
		}
		else {
			url += '&igd=' + val;
		}
		location.href = url;
	},

	onScroll: function() {

		var fix = $(this).scrollTop() >= Supply.headTop - 30;
		if (fix) {
			Supply.table.addClass('fixed');
		}
		else {
			Supply.table.removeClass('fixed');
		}
	}

};