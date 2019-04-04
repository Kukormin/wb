var TableEdit = {
    selectedCount: 0,
    history: [],
    lc: 0,
    rc: 0,
    tr: 0,
    br: 0,
	selectedBody: false,
    selectedTd: false,
    endTd: false,
    currentTd: false,
    editedTd: false,
    selectedRegime: false,
	editMode: true, // Пока всегда включено
    init: function () {
        this.body = $('body');
        this.copyTextarea = $('.copy-textarea textarea');
        this.tables = $('.table-edit');
        this.editDiv = $('.table-edit-input');
        this.editInput = this.editDiv.children('input');
        this.tables.on('mousedown', 'td.e', this.mouseDown);
        this.tables.on('mousemove', 'td.e', this.mouseMove);
        this.tables.on('mouseup', 'td.e', this.mouseUp);
        this.tables.on('dblclick', 'td.e', this.dblClick);
        this.body.on('keydown', this.keyDown);
        this.body.on('mousedown', this.bodyMouseDown);
        this.copyTextarea.on('input', this.paste);
        this.editInput.blur(this.editWordEnd);
        this.editInput.keypress(this.editKey);
    },
    /**
     * Определение левого верхнего и правого нижнего углов
     */
    setSelectedArea: function() {
        var sTdCol = TableEdit.selectedTd.index();
        var sTdRow = TableEdit.selectedTd.parent().index();
        var eTdCol = TableEdit.endTd.index();
        var eTdRow = TableEdit.endTd.parent().index();

        TableEdit.lc = sTdCol;
        TableEdit.rc = eTdCol;
        if (eTdCol < sTdCol) {
            TableEdit.lc = eTdCol;
            TableEdit.rc = sTdCol;
        }
        TableEdit.tr = sTdRow;
        TableEdit.br = eTdRow;
        if (eTdRow < sTdRow) {
            TableEdit.tr = eTdRow;
            TableEdit.br = sTdRow;
        }
    },
    /**
     * Обновляем область выделения над ячейками
     * @returns {boolean}
     */
    selectCells: function() {
        TableEdit.unselectCells();
        for (var row = TableEdit.tr; row <= TableEdit.br; row++) {
            var tr = TableEdit.selectedBody.find('tr:eq(' + row + ')');
            for (var col = TableEdit.lc; col <= TableEdit.rc; col++) {
                var td = tr.find('td:eq(' + col + ')');
                td.addClass('area');
            }
        }
    },
    /**
     * Сбрасываем область выделения
     */
    unselectCells: function() {
        TableEdit.tables.find('td.area').removeClass('area');
    },
    /**
     * Обработка начала выделения
     * @param e
     * @returns {boolean}
     */
    mouseDown: function(e) {
        if (TableEdit.editMode) {
            var td = $(this);
	        TableEdit.selectedBody = td.closest('tbody');
            if (!e.shiftKey) {
                TableEdit.tables.find('td.area').removeClass('area');

                if (TableEdit.selectedTd) {
                    if (TableEdit.selectedTd.get(0) === td.get(0))
                        return false;
                    TableEdit.selectedTd.removeClass('selected');
                }
                TableEdit.selectedTd = td;
                TableEdit.selectedTd.addClass('selected');
            }
            else {
                if (!TableEdit.selectedTd) {
                    TableEdit.selectedTd = td;
                    TableEdit.selectedTd.addClass('selected');
                }
            }
            TableEdit.currentTd = e.target;
            TableEdit.endTd = td;

            TableEdit.selectedRegime = true;
            TableEdit.setSelectedArea();
            if (e.shiftKey)
                TableEdit.selectCells();
        }
    },
    /**
     * Обработка перемещения выделения
     * @param e
     * @returns {boolean}
     */
    mouseMove: function(e) {
        if (TableEdit.editMode) {
            e.stopPropagation();
            var target = e.target;
            var td = $(target);
            if (!td.is('td'))
                return false;

            if (target.getAttribute('unselectable') === 'on')
                target.ownerDocument.defaultView.getSelection().removeAllRanges();

            if (!TableEdit.selectedRegime)
                return false;

            if (TableEdit.currentTd === target)
                return false;

            TableEdit.currentTd = target;
            TableEdit.endTd = td;

            TableEdit.setSelectedArea();
            TableEdit.selectCells();
        }
        return false;
    },
    /**
     * Конец выделения
     * @param e
     * @returns {boolean}
     */
    mouseUp: function(e) {
        if (TableEdit.editMode) {
            e.stopPropagation();
            TableEdit.selectedRegime = false;
        }
        return false;
    },
    dblClick: function() {
        if (TableEdit.editMode) {
            TableEdit.startEditTd($(this), true);
        }
    },
    startEditTd: function(td, exValue) {
        TableEdit.editedTd = td;
        var pos = td.position();
        var text = '';
        if (exValue)
            text = td.text();
        TableEdit.editInput.val(text).width(td.width());
        TableEdit.editDiv.addClass('editting').css({
            top: pos.top,
            left: pos.left
        });
        TableEdit.editInput.focus();
    },
    bodyMouseDown: function(e) {
        TableEdit.tableKeys = !!$(e.target).closest('.table-edit').length;
    },
    keyDown: function(e) {
        if (TableEdit.editMode && TableEdit.selectedTd &&
            TableEdit.tableKeys && e.target.tagName !== 'INPUT') {
            var ctrlKey = e.ctrlKey || e.metaKey;
            var tr = false;
            var nextTr = false;
            var nextTd = false;
            if (e.key === 'ArrowDown') {
                tr = TableEdit.endTd.closest('tr');
                nextTr = tr.next();
                if (nextTr.length)
                    nextTd = nextTr.children('td:eq(' + TableEdit.endTd.index() + ')');
            }
            else if (e.key === 'ArrowUp') {
                tr = TableEdit.endTd.closest('tr');
                nextTr = tr.prev();
                if (nextTr.length)
                    nextTd = nextTr.children('td:eq(' + TableEdit.endTd.index() + ')');
            }
            else if (e.key === 'ArrowLeft') {
                nextTd = TableEdit.endTd.prev();
                if (nextTd.length && !nextTd.is('.e'))
                    nextTd = false;
            }
            else if (e.key === 'ArrowRight') {
                nextTd = TableEdit.endTd.next();
                if (nextTd.length && !nextTd.is('.e'))
                    nextTd = false;
            }
            else if (e.key === 'Tab') {
                nextTd = TableEdit.endTd.next();
                if (nextTd.length && !nextTd.is('.e'))
                    nextTd = false;
                if (!nextTd || !nextTd.length) {
                    tr = TableEdit.endTd.closest('tr');
                    nextTr = tr.next();
                    if (nextTr.length)
                        nextTd = nextTr.children('td.e:first');
                }
            }
            else if (e.key === 'Delete' || e.key === 'Backspace') {
                TableEdit.deleteCells();
                return false;
            }
            else if (e.key === 'Home') {
                tr = TableEdit.endTd.closest('tr');
                nextTd = tr.children('td.e:first');
                if (TableEdit.endTd.index() === nextTd.index())
                    return false;
            }
            else if (e.key === 'End') {
                tr = TableEdit.endTd.closest('tr');
                nextTd = tr.children('td.e:last');
                if (TableEdit.endTd.index() === nextTd.index())
                    return false;
            }
            else if (e.which === 67 && ctrlKey && !e.shiftKey && !e.altKey) {
                TableEdit.copyCells();
            }
            else if (e.which === 86 && ctrlKey && !e.shiftKey && !e.altKey) {
                TableEdit.pasteCells();
            }
            else if (e.which === 88 && ctrlKey && !e.shiftKey && !e.altKey) {
                TableEdit.cutCells();
            }
            else if (e.which === 90 && ctrlKey && !e.shiftKey && !e.altKey) {
                TableEdit.undo();
            }
            else {
                if (!ctrlKey && !e.altKey) {
                    if (e.key.length === 1 || e.key === 'Enter') {
                        TableEdit.startEditTd(TableEdit.selectedTd, e.key === 'Enter');
                        if (e.key === 'Enter')
                            return false;
                    }
                }
            }

            if (nextTd && nextTd.length) {
                if (e.shiftKey) {
                    TableEdit.endTd = nextTd;
                    TableEdit.setSelectedArea();
                    TableEdit.selectCells();
                }
                else {
                    TableEdit.unselectCells();
                    if (TableEdit.selectedTd)
                        TableEdit.selectedTd.removeClass('selected');
                    TableEdit.selectedTd = nextTd;
                    TableEdit.selectedTd.addClass('selected');
                    TableEdit.endTd = nextTd;
                    TableEdit.setSelectedArea();
                }

                return false;
            }
        }
    },
    editWordEnd: function() {
        var oldValue = TableEdit.editedTd.text();
        var value = TableEdit.editInput.val();
        TableEdit.editDiv.removeClass('editting');
        if (oldValue !== value) {
            TableEdit.editedTd.text(value);
            var tr = TableEdit.editedTd.closest('tr');
            var offerId = tr.data('id');
            var storeId = TableEdit.editedTd.data('id');
            var col = TableEdit.editedTd.index();
            var row = TableEdit.editedTd.parent().index();
            var historyItem = [[col, row, oldValue]];
            TableEdit.history.push(historyItem);
            TableEdit.onChange(offerId, storeId, value);
            if (storeId) {
				var post = 'data[' + offerId + '][' + storeId + ']=' + value;
				TableEdit.save(post);
			}
        }
    },
    editKey: function(e) {
        if (TableEdit.editMode) {
            var td = TableEdit.editedTd;
            if (!td)
                return false;

            var tr = false;
            var nextTr = false;
            var nextTd = false;
            if (e.key === 'ArrowDown' || e.key === 'Enter') {
                tr = td.closest('tr');
                nextTr = tr.next();
                if (nextTr.length)
                    nextTd = nextTr.children('td:eq(' + td.index() + ')');
            }
            else if (e.key === 'ArrowUp') {
                tr = td.closest('tr');
                nextTr = tr.prev();
                if (nextTr.length)
                    nextTd = nextTr.children('td:eq(' + td.index() + ')');
            }
            else if (e.key === 'Tab') {
                nextTd = td.next();
                if (nextTd.length && !nextTd.is('.e'))
                    nextTd = false;
                if (!nextTd || !nextTd.length) {
                    tr = td.closest('tr');
                    nextTr = tr.next();
                    if (nextTr.length)
                        nextTd = nextTr.children('td.e:first');
                }
            }

            if (nextTd && nextTd.length) {
                TableEdit.editWordEnd();

                if (TableEdit.selectedTd)
                    TableEdit.selectedTd.removeClass('selected');
                TableEdit.selectedTd = nextTd;
                TableEdit.selectedTd.addClass('selected');
                TableEdit.endTd = nextTd;
                TableEdit.setSelectedArea();

                e.stopPropagation();
                return false;
            }
        }
    },
    /**
     * Удаление значений в выбранных ячейках
     * @returns {boolean}
     */
    deleteCells: function() {
        var historyItem = [];
        var post = '';
        var checkRows = {};
        for (var row = TableEdit.tr; row <= TableEdit.br; row++) {
            var tr = TableEdit.selectedBody.find('tr:eq(' + row + ')');
            var offerId = tr.data('id');
            for (var col = TableEdit.lc; col <= TableEdit.rc; col++) {
                var td = tr.find('td:eq(' + col + ')');
                var storeId = td.data('id');
                var oldText = td.text();
                if (oldText !== '') {
                    td.text('');
                    historyItem.push([col, row, oldText]);
                    checkRows[row] = tr;
					TableEdit.onChange(offerId, storeId, 0);

					if (storeId) {
						if (post)
							post += '&';
						post += 'data[' + offerId + '][' + storeId + ']=';
					}
                }
            }
        }

        if (historyItem.length) {
            TableEdit.history.push(historyItem);
            if (post)
            	TableEdit.save(post);
        }
    },
    /**
     * Копирование выделенных ячеек
     * @returns {boolean}
     */
    copyCells: function() {
        var text = '';
        for (var row = TableEdit.tr; row <= TableEdit.br; row++) {
            var tr = TableEdit.selectedBody.find('tr:eq(' + row + ')');
            for (var col = TableEdit.lc; col <= TableEdit.rc; col++) {
                var td = tr.find('td:eq(' + col + ')');
                var sep = col === TableEdit.rc ? (row === TableEdit.br ? '' : '\n') : '\t';
                var oldText = td.text().trim();
                text += oldText + sep;
            }
        }
        TableEdit.copyTextarea.val(text).focus();
        TableEdit.copyTextarea.get(0).select();
    },
    /**
     * Вырезать (копирование + очистка ячеек)
     */
    cutCells: function() {
        var text = '';
        var historyItem = [];
        var post = '';
        var checkRows = {};
        for (var row = TableEdit.tr; row <= TableEdit.br; row++) {
            var tr = TableEdit.selectedBody.find('tr:eq(' + row + ')');
            var offerId = tr.data('id');
            for (var col = TableEdit.lc; col <= TableEdit.rc; col++) {
                var td = tr.find('td:eq(' + col + ')');
                var sep = col === TableEdit.rc ? (row === TableEdit.br ? '' : '\n') : '\t';
                var storeId = td.data('id');
                var oldText = td.text().trim();
                if (oldText !== '') {
                    td.text('');
                    historyItem.push([col, row, oldText]);
					TableEdit.onChange(offerId, storeId, 0);
                    checkRows[row] = tr;
                    if (storeId) {
						if (post)
							post += '&';
						post += 'data[' + offerId + '][' + storeId + ']=';
					}
                }
                text += oldText + sep;
            }
        }
        TableEdit.copyTextarea.val(text).focus();
        TableEdit.copyTextarea.get(0).select();
        if (historyItem.length) {
            TableEdit.history.push(historyItem);
            if (post)
            	TableEdit.save(post);
        }
    },
    /**
     * Вставка
     */
    pasteCells: function() {
        TableEdit.copyTextarea.val('').focus();
        TableEdit.pasteRegime = true;
    },
    paste: function() {
        if (TableEdit.pasteRegime) {
            var historyItem = [];
            var post = '';
            var colRange = TableEdit.rc - TableEdit.lc;
            var rowRange = TableEdit.br - TableEdit.tr;
            var inRange = colRange > 0 || rowRange > 0;

            var text = TableEdit.copyTextarea.val();
            var rows = text.split('\n');
            var rowsCount = rows.length;
            var checkRows = {};

            for (var i = 0; i < rowsCount; i++) {
                if (inRange && i > rowRange)
                    break;

                var row = TableEdit.tr + i;
                var tr = TableEdit.selectedBody.find('tr:eq(' + row + ')');
                if (!tr.length)
                    break;

                var offerId = tr.data('id');

                var cols = rows[i].split('\t');
                var colsCount = cols.length;

                for (var j = 0; j < colsCount; j++) {
                    if (inRange && j > colRange)
                        break;

                    var col = TableEdit.lc + j;
                    var td = tr.find('td:eq(' + col + ')');
                    if (!td.length || !td.is('.e'))
                        break;

                    var value = cols[j];
                    var oldValue = '';
                    var change = false;
                    var storeId = td.data('id');
                    oldValue = td.text();
                    if (oldValue !== value) {
                        td.text(value);
                        change = true;
                    }

                    if (change) {
                        historyItem.push([col, row, oldValue]);
						TableEdit.onChange(offerId, storeId, value);
                        checkRows[row] = tr;
                        if (storeId) {
							if (post)
								post += '&';
							post += 'data[' + offerId + '][' + storeId + ']=' + value;
						}
                    }
                }
            }
            TableEdit.pasteRegime = false;

            if (historyItem.length) {
                TableEdit.history.push(historyItem);
                if (post)
                	TableEdit.save(post);
            }
        }
    },
    /**
     * Отмена изменений
     */
    undo: function() {
        if (!TableEdit.history.length)
            return;
        var historyItem = TableEdit.history.pop();
        if (!historyItem)
            return;
        var l = historyItem.length;
        var post = '';
        var checkRows = {};
        for (var i = 0; i < l; i++) {
            var col = historyItem[i][0];
            var row = historyItem[i][1];
            var value = historyItem[i][2];
            var tr = TableEdit.selectedBody.find('tr:eq(' + row + ')');
            var offerId = tr.data('id');
            var td = tr.find('td:eq(' + col + ')');
            var storeId = td.data('id');
            if (td.length) {
                td.text(value);
				TableEdit.onChange(offerId, storeId, value);
                checkRows[row] = tr;
                if (storeId) {
					if (post)
						post += '&';
					post += 'data[' + offerId + '][' + storeId + ']=' + value;
				}
            }
        }
        if (post) {
            TableEdit.save(post);
        }
    },
	save: function(post) {
		$.ajax({
			url: '/ajax/target_save.php',
            type: 'post',
			data: post
		});
	},
	onChange: function(offerId, storeId, value) {
		$(document).trigger('table.edit', [offerId, storeId, value]);
    }
};