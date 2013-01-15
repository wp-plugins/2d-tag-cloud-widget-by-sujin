<?php
// [tag2d number="" separator="" sort=""]
// sort = name || DESC || intersection
function sjTagShortcode($atts) {
	extract(shortcode_atts(array(
		'number' => '30',
		'separator' => '',
		'sort' => 'name'
	), $atts));

	$number = (float) $number;
	if ($number < 1) $number = 30;
	if ($sort != 'DESC' && $sort != 'intersection') $sort = 'name';

	$tags_out = sjGetTags($number, $separator, $sort);

	echo '<div class="tag_cloud">' . $tags_out . '</div>';
}
add_shortcode('tag2d', 'sjTagShortcode');