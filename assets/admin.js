// initialize

jQuery(document).ready(function($){
	sjBindSpinner();
	sjBindAris();
	sjSetStyle();
	sjSetPreview();
});

function sjBindSpinner() {
	if (parseFloat(jQuery.ui.version) >= 1.9) {
		jQuery( ".jquery-spinner" ).spinner({
	    	stop: function( event, ui ) {
				if (jQuery(this).attr('id') == 'tag_step') {
					if (jQuery(this).val() < 1) {
						jQuery(this).val(1);
						return false;
					} else {
						sjResetStep();
					}
				} else {
					if (jQuery(this).val() < 0) {
						jQuery(this).val(0);
						return false;
					}
				}
	
				sjSetStyle();
	    	}
		});
	} else {
		jQuery('.tag_radius, .tag_padding, .tag_size, .jquery-spinner').bind('blur', function() {
			if (jQuery(this).attr('id') == 'tag_step') {
				if (jQuery(this).val() < 1) {
					jQuery(this).val(1);
					return false;
				} else {
					sjResetStep();
				}
			}

			sjSetStyle();
		});
	}
}

function sjBindAris() {
	if (parseFloat(jQuery.ui.version) >= 1.9) {
		jQuery('.color-picker').iris({
			hide: true,
			change: function(event, ui) {
				sjSetStyle();
			}
		}).bind('focus', function() {
			jQuery('.iris-picker').hide();
			jQuery(this).next().show();
		});

		jQuery("body").bind('click', function(e) {
			if (!jQuery(e.target).hasClass('iris-picker') && !jQuery(e.target).hasClass('color-picker')) {
				jQuery(".iris-picker").hide();
			}
		});
	}
}

// 스텝을 늘리거나 줄이면 움찔!
function sjResetStep() {
	// 깔린 스텝과 인풋에서 설정된 스텝
	var count = jQuery('#tag_step').val();
	var prevCount = jQuery('#sjTagTable thead tr th').length - 1;

	// 예전 게 많으면 하나 없앤다
	if (prevCount > count) {
		for (i=prevCount; i>count; i--) {
			jQuery('#sjTagTable thead tr th:last').remove();
			jQuery('#sjTagTable tbody tr').each(function() {
				jQuery(this).find('td:last').remove();
			});
		}

	// 예전 게 적으면 하나 만든다
	} else if (prevCount != count) {
		for (i=prevCount; i<count; i++) {
			targetId = i + 1;

			jQuery('#sjTagTable thead tr th:last').clone().appendTo('#sjTagTable thead tr');
			jQuery('#sjTagTable tbody tr').each(function() {
				jQuery(this).find('td:last').clone().appendTo(jQuery(this));
				jQuery(this).find('td:last .iris-picker').remove();
				jQuery(this).find('td:last .ui-spinner-button').remove();
			});

			// 아이디 바꾸고, 글 바꾸고
			jQuery('#sjTagTable thead tr th:last').attr('id', 'tag_step_' + targetId + '_preview');
			jQuery('#sjTagTable thead tr th:last').html('<span>' + jQuery('#text_of_step').val() + ' ' + targetId + '</span>');

			// 인풋 바꾸고
			jQuery('#sjTagTable tbody tr').each(function() {
				jQuery(this).find('td:last .tag_color').attr('id', 'tag_color_step_' + targetId).attr('name', 'tag_color_step_' + targetId).val('#000000');
				jQuery(this).find('td:last .tag_bgcolor').attr('id', 'tag_bgcolor_step_' + targetId).attr('name', 'tag_bgcolor_step_' + targetId).val('');
				jQuery(this).find('td:last .tag_radius').attr('id', 'tag_radius_step_' + targetId).attr('name', 'tag_radius_step_' + targetId).val(0);
				jQuery(this).find('td:last .tag_padding').attr('id', 'tag_padding_step_' + targetId).attr('name', 'tag_padding_step_' + targetId).val(0);
				jQuery(this).find('td:last .tag_size').attr('id', 'tag_size_step_' + targetId).attr('name', 'tag_size_step_' + targetId).val(12);
			});
		}
	}

    sjBindSpinner();
    sjBindAris();
    sjSetStyle();
}

function sjSetStyle(selector) {
	jQuery('#sjTagTable input').each(function() {
		var id = jQuery(this).attr('id');
		var exp = id.split('_step_');

		switch (exp[0]) {
			case 'tag_color' :
				var color = jQuery(this).val();
				if (!color) color = 'black';
				jQuery('#tag_step_' + exp[1] + '_preview span').css({ 'color' : color });
				break;

			case 'tag_bgcolor' :
				var bgcolor = jQuery(this).val();
				if (!bgcolor) bgcolor = 'transparent';
				jQuery('#tag_step_' + exp[1] + '_preview span').css({ 'background-color' : bgcolor });
				break;

			case 'tag_radius' :
				var radius = Math.floor(jQuery(this).val());
				jQuery('#tag_step_' + exp[1] + '_preview span').css({ 'borderRadius' : radius + 'px' });
				break;

			case 'tag_padding' :
				var padding = Math.floor(jQuery(this).val());
				var padding2 = padding + 2;

				padding = padding + 'px ' + padding2 + 'px';

				jQuery('#tag_step_' + exp[1] + '_preview span').css({ 'padding' : padding });
				break;

			case 'tag_size' :
				var size = Math.floor(jQuery(this).val());
				jQuery('#tag_step_' + exp[1] + '_preview span').css({ 'font-size' : size + 'px' });
				break;
		}
	});
}

function sjSetPreview() {
	var count = jQuery('#sjTagTable thead tr th').length - 1;

	jQuery('#sjTagPreview a').each(function(i) {
		var rnd_size = Math.floor(Math.random() * count) + 1;
		var rnd_color = Math.floor(Math.random() * count) + 1;

		var color = jQuery('#tag_color_step_' + rnd_color).val();
		if (!color) color = 'black';
	
		var bgcolor = jQuery('#tag_bgcolor_step_' + rnd_color).val();
		if (!bgcolor) bgcolor = 'transparent';
	
		var radius = Math.floor(jQuery('#tag_radius_step_' + rnd_color).val());
	
		var padding = Math.floor(jQuery('#tag_padding_step_' + rnd_color).val());
		var padding2 = padding + 2;
	
		padding = padding + 'px ' + padding2 + 'px';
	
		var size = Math.floor(jQuery('#tag_size_step_' + rnd_size).val());

		if (i != 0) {
			var margin_left = jQuery('#margin_right').val() + 'px';
		} else {
			var margin_left = 0;
		}

		jQuery(this).css({
			'color' : color,
			'background-color' : bgcolor,
			'borderRadius' : radius + 'px',
			'padding' : padding,
			'font-size' : size + 'px',
			'line-height' : jQuery('#line_height').val() + jQuery('#line_height_unit').val(),
			'margin-left' : margin_left,
			'margin-bottom' : jQuery('#margin_bottom').val() + 'px',
		});
	});
}

function do_preset_4_white() {
	jQuery('#tag_step').val(4);
	sjResetStep();

	jQuery('#line_height').val(1.3);
	jQuery('#line_height_unit').val('em');
	jQuery('#margin_right').val(5);
	jQuery('#margin_bottom').val(10);

	jQuery('#tag_color_step_1').val('#cecece');
	jQuery('#tag_bgcolor_step_1').val('');
	jQuery('#tag_radius_step_1').val('');
	jQuery('#tag_padding_step_1').val(0);

	jQuery('#tag_color_step_2').val('#856797');
	jQuery('#tag_bgcolor_step_2').val('');
	jQuery('#tag_radius_step_2').val('');
	jQuery('#tag_padding_step_2').val(0);

	jQuery('#tag_color_step_3').val('#FFFFFF');
	jQuery('#tag_bgcolor_step_3').val('#c9bbd2');
	jQuery('#tag_radius_step_3').val(5);
	jQuery('#tag_padding_step_3').val(3);

	jQuery('#tag_color_step_4').val('#FFFFFF');
	jQuery('#tag_bgcolor_step_4').val('#7629A3');
	jQuery('#tag_radius_step_4').val(5);
	jQuery('#tag_padding_step_4').val(3);

	jQuery('#tag_size_step_1').val(12);
	jQuery('#tag_size_step_2').val(16);
	jQuery('#tag_size_step_3').val(21);
	jQuery('#tag_size_step_4').val(26);

	sjResetStep();
	sjSetPreview();
}

function do_preset_4_black() {
	jQuery('#tag_step').val(4);
	sjResetStep();

	jQuery('#line_height').val(1.3);
	jQuery('#line_height_unit').val('em');
	jQuery('#margin_right').val(5);
	jQuery('#margin_bottom').val(10);

	jQuery('#tag_color_step_1').val('#CECECE');
	jQuery('#tag_bgcolor_step_1').val('');
	jQuery('#tag_radius_step_1').val('');
	jQuery('#tag_padding_step_1').val(0);

	jQuery('#tag_color_step_2').val('#FFFFFF');
	jQuery('#tag_bgcolor_step_2').val('');
	jQuery('#tag_radius_step_2').val('');
	jQuery('#tag_padding_step_2').val(0);

	jQuery('#tag_color_step_3').val('#FFFFFF');
	jQuery('#tag_bgcolor_step_3').val('#fde394');
	jQuery('#tag_radius_step_3').val(5);
	jQuery('#tag_padding_step_3').val(3);

	jQuery('#tag_color_step_4').val('#FFFFFF');
	jQuery('#tag_bgcolor_step_4').val('#fcc111');
	jQuery('#tag_radius_step_4').val(5);
	jQuery('#tag_padding_step_4').val(3);

	jQuery('#tag_size_step_1').val(12);
	jQuery('#tag_size_step_2').val(16);
	jQuery('#tag_size_step_3').val(21);
	jQuery('#tag_size_step_4').val(26);

	sjResetStep();
	sjSetPreview();
}

function delete_set(set_num) {
	if (set_num == 0) {
		alert (jQuery('#text_of_delete_alert').val());
		return false;
	}

	if (confirm(jQuery('#text_of_delete_confirm').val())) {
		jQuery('input[name="action"]').val("delete");
		jQuery('input[name="set_current_id"]').val(set_num);
		jQuery('#submit').trigger('click');
	}
}

function make_set() {
	if (!jQuery('#set_name').val()) {
		alert(jQuery('#text_of_make_alert').val());
		return false;
	}

	jQuery('input[name="action"]').val("makenew");
	jQuery('#sjTagForm').attr('action', jQuery('input[name="_wp_http_referer"]').val());
	jQuery('#submit').trigger('click');

	return true;
}