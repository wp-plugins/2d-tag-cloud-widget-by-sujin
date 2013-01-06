// initialize
var selectedObj;
var jQueryUIVersion;

jQuery(document).ready(function($){
	jQueryUIVersion = parseFloat(jQuery.ui.version);
	// 리로딩 해도 값은 똑같도록... (If you don't know Korean, ask to twitter what it means!)
	bindObject();
	bindActiveObject();
	setStyle('all');
});

// 오브젝트에 기능을 연결한다
function bindObject() {
	// 캔슬하면 투명하게
	jQuery('.tag_bgcolor_cancel').bind('click', function() {
		jQuery('#tag_bgcolor').val('');
		selectedObj.find('.tag_bgcolor').val('');

		setStyle();
		return false;
	});

	// 스피너 (인풋 옆에 나타나는 위/아래 화살표)
	if (jQueryUIVersion >= 1.9) {
		jQuery( ".jquery-spinner" ).spinner({
	    	stop: function( event, ui ) {
				if (jQuery(this).attr('id') == 'tag_step') {
					if (jQuery(this).val() < 1) {
						jQuery(this).val(1);
						return false;
					} else {
						resetStep();
					}
				}
	
				setStyle();
	    	}
	    });
	
	    // 컬러 찍기 아이리스
		jQuery('.color-picker').iris({
			hide: false,
			change: function(event, ui) {
				setStyle();
			}
		});
	} else {
		jQuery('#tag_step').bind('blur', function() {
			if (jQuery(this).val() < 1) {
				jQuery(this).val(1);
				return false;
			} else {
				resetStep();
			}
		});
	}

	// 래디어스, 패딩, 사이즈
	jQuery('#tag_radius, #tag_padding, #tag_size').bind('keyup click blur focus change paste', function() {
		setStyle();
	});
}

function bindActiveObject() {
	jQuery('#tag_color_step li').bind('click', function() {
		selectedObj = jQuery(this);
		jQuery('.modal').hide();

		jQuery('#color_selector #tag_color').val(jQuery(this).find('.tag_color').val());
		jQuery('#color_selector #tag_bgcolor').val(jQuery(this).find('.tag_bgcolor').val());
		jQuery('#color_selector #tag_radius').val(jQuery(this).find('.tag_radius').val());
		jQuery('#color_selector #tag_padding').val(jQuery(this).find('.tag_padding').val());

		if (jQueryUIVersion >= 1.9) {
			jQuery('#tag_color').iris({
				color: jQuery('#tag_color').val(),
				hide: false,
				change: function(event, ui) {
					setStyle();
				}
			});
		}

		if (jQueryUIVersion >= 1.9) {
			jQuery('#tag_bgcolor').iris({
				color: jQuery('#tag_bgcolor').val(),
				hide: false,
				change: function(event, ui) {
					setStyle();
				}
			});
		}

		jQuery('#color_selector').show();
	});

	jQuery('#tag_size_step li').bind('click', function() {
		selectedObj = jQuery(this);
		jQuery('.modal').hide();
		
		jQuery('#size_selector #tag_size').val(jQuery(this).find('.tag_size').val());

		jQuery('#size_selector').show();
	});
}

// 스텝을 늘리거나 줄이면 움찔!
function resetStep() {
	// 깔린 스텝과 인풋에서 설정된 스텝
	var count = jQuery('#tag_step').val();
	var prevCount = jQuery('#tag_color_step li').length;

	// 예전 게 많으면 하나 없앤다
	if (prevCount > count) {
		for (i=prevCount; i>count; i--) {
			jQuery('#tag_color_step li:last, #tag_size_step li:last-child').remove();
		}

	// 예전 게 적으면 하나 만든다
	} else if (prevCount != count) {
		for (i=prevCount; i<count; i++) {
			targetId = i + 1;
	
			// 컬러 클론 뜨기
			jQuery('#tag_color_step li:last').clone().appendTo(jQuery('#tag_color_step'));
			var lastElm = jQuery('#tag_color_step li:last');
	
			// 아이디 바꾸고, 글 바꾸고
			lastElm.attr('id', 'tag_color_step_' + targetId + '_preview');
			lastElm.find('span').html('Step ' + targetId);
	
			// 인풋 바꾸고
			lastElm.find('.tag_color').attr('id', 'tag_color_step_' + targetId).attr('name', 'tag_color_step_' + targetId).val('#000000');
			lastElm.find('.tag_bgcolor').attr('id', 'tag_bgcolor_step_' + targetId).attr('name', 'tag_bgcolor_step_' + targetId).val('');
			lastElm.find('.tag_radius').attr('id', 'tag_radius_step_' + targetId).attr('name', 'tag_radius_step_' + targetId).val(0);
			lastElm.find('.tag_padding').attr('id', 'tag_padding_step_' + targetId).attr('name', 'tag_padding_step_' + targetId).val(0);
			
			// 사이즈 클론 뜨기
			jQuery('#tag_size_step li:last').clone().appendTo(jQuery('#tag_size_step'));
			var lastElm = jQuery('#tag_size_step li:last');
	
			// 아이디 바꾸고, 글 바꾸고
			lastElm.attr('id', 'tag_size_step_' + targetId + '_preview');
			lastElm.find('span').html('Step ' + targetId);
	
			// 인풋 바꾸고
			lastElm.find('.tag_size').attr('id', 'tag_size_step_' + targetId).attr('name', 'tag_size_step_' + targetId).val(12);
			jQuery('#tag_step').val(targetId);
		}
	}

	setStyle('all');
	bindActiveObject();
}

// 단지 스타일 뿐 아니라 생성되는 오브젝트의 액션 까지
function setStyle(selector) {
	if (selectedObj) {
		selectedObj.find('.tag_color').val(jQuery('#tag_color').val());
		selectedObj.find('.tag_bgcolor').val(jQuery('#tag_bgcolor').val());
		selectedObj.find('.tag_radius').val(jQuery('#tag_radius').val());
		selectedObj.find('.tag_padding').val(jQuery('#tag_padding').val());
		selectedObj.find('.tag_size').val(jQuery('#tag_size').val());
	}

	if (selector == 'all') {
		selector_1 = jQuery('#tag_color_step li');
		selector_2 = jQuery('#tag_size_step li');
	} else {
		selector_1 = selectedObj;
		selector_2 = selectedObj;
	}
	// 스타일 히든 값을 span에 지정
	if (selector_1) {
		selector_1.each(function(){
			var span = jQuery(this).find('span');

			if (!jQuery(this).find('.tag_bgcolor').val()) {
				var background_color = 'transparent';
			} else {
				var background_color = jQuery(this).find('.tag_bgcolor').val();
			}

			var padding = Math.floor(jQuery(this).find('.tag_padding').val());
			padding2 = padding + 2;
			padding = padding + 'px ' + padding2 + 'px';

			span.css({
				'color' : jQuery(this).find('.tag_color').val(),
				'background-color' : background_color,
				'borderRadius' : jQuery(this).find('.tag_radius').val() + 'px',
				'padding' : padding
			});
		});
	}

	if (selector_2) {
		selector_2.each(function(){
			var span = jQuery(this).find('span');

			span.css({
				'font-size' : jQuery(this).find('.tag_size').val() + 'px'
			});
		});
	}
}

function do_preset_4_white() {
	jQuery('#tag_step').val(4);
	resetStep();
	
	jQuery('#tag_color_step_1').val('#cecece');
	jQuery('#tag_bgcolor_step_1').val('');
	jQuery('#tag_radius_step_1').val('');
	jQuery('#tag_padding_step_1').val(0);

	jQuery('#tag_color_step_2').val('#856797');
	jQuery('#tag_bgcolor_step_2').val('');
	jQuery('#tag_radius_step_2').val('');
	jQuery('#tag_padding_step_2').val(0);

	jQuery('#tag_color_step_3').val('#ffffff');
	jQuery('#tag_bgcolor_step_3').val('#c9bbd2');
	jQuery('#tag_radius_step_3').val(5);
	jQuery('#tag_padding_step_3').val(3);

	jQuery('#tag_color_step_4').val('#ffffff');
	jQuery('#tag_bgcolor_step_4').val('#7629A3');
	jQuery('#tag_radius_step_4').val(5);
	jQuery('#tag_padding_step_4').val(3);

	jQuery('#tag_size_step_1').val(12);
	jQuery('#tag_size_step_2').val(16);
	jQuery('#tag_size_step_3').val(21);
	jQuery('#tag_size_step_4').val(26);

	resetStep();
}