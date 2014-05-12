/*	jQuery Config 0.0.1
	Update: 2012-10-02
*/

$(document).ready(function() {
	$('#f-config').ajaxForm({ url:ajaxurl, dataType:'json', data:{cmd:'saveConfig'},
		beforeSend: function() {
		},
		success: function(data) {
			msg.showAjax(data);
		}
	});

// ADD VARIABLE to config ---------------------------------------------------------------
	allFields = $([]).add($('#var-name')).add($('#var-title')).add($('#var-value')),

	$('#f-addvariable').ajaxForm({ url:ajaxurl, dataType:'json', data:{cmd:'addvariable'}, type:'POST',
		beforeSend: function() {
		},
		success: function(data) {
			msg.showAjax(data);

			//	Подсвечивание полей ввода в форме
			if (data.highlight)
				for (var key in data.highlight) {
					$('#var-'+key).addClass("ui-state-error");
				}

//			if (data.type_msg == "ok")
//				$('#dialog-form').dialog("close");
		}
	});

	$("#var-type").change(function() {
		var val = $(this, "option:selected").val();
		if (val=='S' || val=='R')
			$('#var-tooltip').show();
		else
			$('#var-tooltip').hide();
	});

	$("#dialog-form").dialog({
		autoOpen: false,
		height: 400,
		width: 500,
		modal: true,
		buttons: {
			"Сохранить": function() {
				allFields.removeClass("ui-state-error");

				$('#f-addvariable').submit();
			},
			Cancel: function() {
				$(this).dialog("close");
			}
		},
		close: function() {
			allFields.val("").removeClass("ui-state-error");
			hideMsg();
		}
	});

	$("#create-variable").click(function() {
		$("#dialog-form").dialog("open");
	});
});