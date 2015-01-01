/*	jQuery Message 0.0.11
	Update 0.0.11: 2014-11-29
	Update 0.0.10: 2014-05-03
	Update 0.0.9: 2014-01-07
	Update 0.0.8: 2013-10-01
	Update 0.0.7: 2013-09-29
	Update 0.0.6: 2012-11-26
	Update 0.0.5: 2012-10-08
	Update 0.0.4: 2012-09-27
	Update 0.0.3: 2012-04-30
	Date start: 2012-03-21
*/

var msg = {
	timec: new Date(),
	type_last: '',
	width: 300,
	stylerender: 'roll',
	speed: 500,
	msg_successfully: false,
	auto_hide: true,
	defaultMsg: '#js-msg',

	showAjax: function (data, obj) {

		if (typeof(obj) === 'object')
			var msgObj = obj.is('form') ? obj.prev('.msg') : obj;


		if (data.msgType) {
			switch (data.msgType) {
				case 'error':
					this.show(data.error, 'error', msgObj);
				break;

				case 'ok':
					this.show(data.ok, 'ok', msgObj);
					// if (data.reload!==undefined) {
					// 	if (data.reload===true) {
					// 		$('#js-msgbox'+msgObj).before('<div id="ui-background" class="ui-widget-overlay" style="z-index:'+$('#js-msgbox'+msgObj).css('z-index')+'"><div>Перезагрузка страницы...</div></div>');
					// 		setTimeout('location.replace("")', 800);
					// 	}
					// 	else if (typeof(data.reload)==='string') {
					// 		$('#js-msgbox'+msgObj).before('<div id="ui-background" class="ui-widget-overlay" style="z-index:'+$('#js-msgbox'+msgObj).css('z-index')+'"><div>Перезагрузка страницы...</div></div>');
					// 		setTimeout('location.replace("'+data.reload+'")', 800);
					// 	}
					// }

				break;

				case 'info':
					this.show(data.info, 'info', msgObj);
				break;

				case 'warning':
					this.show(data.warning, 'warning', msgObj);
				break;
			}
		}
		else
			this.show('Нет ответа от сервера', 'warning', msgObj);

		this.type_last = data.msgType;
	},

	show: function (data, type, obj) {
		if (typeof(obj) === 'object')
			var msgObj = obj.is('form') ? obj.prev('.msg') : obj;
		else
			msgObj = $(this.defaultMsg);

		var msgText = msgObj.children('.msg__text');
		var text = '',
			h = 0;
	//	var btn_submit = $('#js-submit'+prefix);

		if (this.stylerender === 'roll')
			msgObj.css({'width': this.width+'px', 'left': '50%', 'margin-left': '-'+(Math.round(this.width/2))+'px'});
		msgObj.addClass('msg_'+type);

		if (type !== this.type_last)
			msgObj.removeClass('msg_'+this.type_last);

		if (type === 'loading') {
			data = data || 'Загрузка...';
			msgText.html(data);
			h = msgObj.height()+25;
			this.renderMsg(msgObj, h, this.speed);
	//		msgObj.before('<div id="ui-background" class="ui-widget-overlay" style="z-index:'+msgObj.css('z-index')+'"><div>Загрузка...</div></div>');
		}
		else {
			if (typeof(data) === 'object') {
				if (data.length === 1)
					text = data[0];
				else {
					for (i=0; i<data.length; i++) {
						text += '<li>'+data[i]+'</li>';
					}
					text = '<ul>'+text+'</ul>';
				}
			}
			else
				text = data;

			msgText.html(text);

			msgObj.stopTime('msgTimer');
			h = msgObj.height()+25;

			if (type === 'ok') {
				if (this.msg_successfully === true) {
					btn_submit.hide();
					btn_submit.next('.msg-successfully').fadeIn(this.speed);
					this.hideMsg(msgObj, h, 300);
				}
				else {
					this.renderMsg(msgObj, h, this.speed);

					if (this.auto_hide === true) {
						msgObj.oneTime("5s", 'msgTimer', function() {
							msg.hideMsg(msgObj, h, 1000);
						});
					}
				}
			}
			else if (type === 'info') {
				this.renderMsg(msgObj, h, this.speed);

				if (this.auto_hide === true) {
					msgObj.oneTime("5s", 'msgTimer', function() {
						this.hideMsg(msgObj, h, 1000);
					});
				}
			}
			else {
				this.renderMsg(msgObj, h, this.speed);
			}
		}
		this.type_last = type;
	},

	renderMsg: function (msgObj, h, speed) {
		switch (this.stylerender) {
			case 'roll':
				msgObj.addClass('msg_fixed');
//				if (parseInt(msgObj.css('top'))<=0) {
					msgObj.css('top', '-'+h+'px').show();
					msgObj.animate({'top':'0px'}, this.speed);
//				}
			break;
			case 'fade':
				msgObj.fadeIn(speed);
			break;
			case 'slide':
				msgObj.slideDown(speed, 'linear');
			break;
			default:
				msgObj.show();
			break;
		}
	},

	hideMsg: function (msgObj, h, speed) {
		msgObj = msgObj || defaultMsg;
		speed = speed || 1000;
		switch (this.stylerender) {
			case 'roll':
				msgObj.animate({'top': '-'+h+'px'}, speed);
			break;
			case 'slide':
				msgObj.slideUp();
			break;
			case 'fade':
				msgObj.fadeOut(speed);
			break;
			default:
				msgObj.hide();
			break;
		}
	},

	checkMsg: function () {
		if ($('.msg__text').html()) {
			$('.msg').show();
			$('.msg').oneTime('5s', 'msgTimer', function() {
				this.hideMsg($('.msg'));
			});
		}
		else
			$('.msg').hide();
	}
}

msg.checkMsg();
