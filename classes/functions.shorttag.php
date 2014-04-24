<?php
// [tag2d number="" separator="" sort="" set=""]
// sort = name || DESC || intersection

function SJ2DTAG_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'number' => '30',
		'separator' => '',
		'sort' => 'name',
		'set' => false
	), $atts ) );

	$number = (float) $number;
	if ( $number < 1 ) $number = 20;

	$options = compact( 'set', 'number', 'separator', 'sort' );
	return SJ2DTAG_functions::get_tag_cloud( $options );
}
