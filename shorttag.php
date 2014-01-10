<?php
// [tag2d number="" separator="" sort="" set=""]
// sort = name || DESC || intersection

function sj_GetTagCloudByShortcode($atts) {
	extract(shortcode_atts(array(
		'number' => '30',
		'separator' => '',
		'sort' => 'name',
		'set' => 'Default Set'
	), $atts));

	$number = (float) $number;
	if ($number < 1) $number = 30;
	if ($sort != 'DESC' && $sort != 'intersection') $sort = 'name';

	global $sj2DTag;
	$sj2DTag->set_by_name($set);
	$sj2DTag->set_cloud_option($number, $separator, $sort);

	return $sj2DTag->get_tag_cloud();
}
add_shortcode('tag2d', 'sj_GetTagCloudByShortcode');