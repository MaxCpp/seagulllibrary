/*	jQuery EditTable 0.0.1
	Update: 2012-10-02
*/

//var ajaxurl = '/assets/modules/seagullreviews/ajax.php';	NEED SET VARIABLE ajaxurl!!!!

$(document).ready(function() {
	$('ul.b-tabs__buttons').on('click', 'li:not(.b-tabs__button_current)', function() {
		$(this).addClass('b-tabs__button_current').siblings().removeClass('b-tabs__button_current').parents('div.b-tabs').find('div.b-tabs__page').hide().eq($(this).index()).show();
	})

//	Смена статуса "Опубликован/Скрыт"
	$(document).on('click', 'div.b-published, div.b-unpublished', function() {
		var row = $(this);
		var state = row.hasClass('b-published') ? 0 : 1;
		var rowID = row.parent('td').parent('tr').attr('id').replace('row', '');

		$.ajax({ type:'POST', url:ajaxurl, dataType:'json',
			data: {cmd:'setPublished', itemID:rowID, val:state},
			success: function(data) {
				msg.showAjax(data);
				if (data.msgType=='ok') {
					row.toggleClass('b-published b-unpublished');
				}
			},
			error: function(data){
				msg.show('Ошибка при отправке запроса', 'error');
			}
		})
	})

//	Подсказка в которой выводиться поле с классом .nowrap, т.е. который не вмещается в поле td
	$(document).on({
		'mouseenter':function(event) {
			if ($(this).text())
				$(this).prepend('<div id="tooltip-nowrap" style="width:'+$(this).css('width')+'">'+$(this).text()+'</div>');
		},
		'mouseleave':function() {
			$('#tooltip-nowrap').remove();
		}
	}, 'td.nowrap');

//	Постраничная навигация
	$(document).on('click', 'a.paginator-link', function() {
		if (!$(this).hasClass('paginator-link_disabled')) {
			var page = $(this).attr('href').replace('#page', '');

			var param = $(this).parent('div').attr('id');
			if (param !== undefined)
				param = param.replace('paginator-', '');

			$.ajax({ type:'POST', url:ajaxurl, dataType:'json',
				data: {cmd:'getPaginatorPage', pageID:page, param:param},
				beforeSend: function() {
					$('span.paginator-loading').show();
				},
				success: function(data) {
					if (data.error)
						msg.showAjax(data);

//					param = param ? '-'+param : '';
					$('table.tpaginator').children('tbody').html(data.tbody);
					$('div.paginator').html(data.links);
				},
				error: function(data) {
					msg.show('Ошибка при отправке запроса', 'error');
				}
			});
		}
		return false;
	})
});
