<?php
// [tag2d number="" separator="" sort="" set=""]
// sort = name || DESC || intersection
function sjTagShortcode($atts) {
	extract(shortcode_atts(array(
		'number' => '30',
		'separator' => '',
		'sort' => 'name',
		'set' => 'Default Set'
	), $atts));

	$tag_set = get_option('sj_tag_set');
	$set_num = 0;

	foreach($tag_set as $key => $value) {
		if ($value == $set) {
			$set_num = $key;
			break;
		}
	}

	$number = (float) $number;
	if ($number < 1) $number = 30;
	if ($sort != 'DESC' && $sort != 'intersection') $sort = 'name';

	$tags_out = sjGetTags($number, $separator, $sort, $set_num);

	return '<div class="tag_cloud sj_tagcloud_set_' . $set_num . '">' . $tags_out . '</div><style>' . sjPrintCSS($set_num) . '</style>';
}
add_shortcode('tag2d', 'sjTagShortcode');