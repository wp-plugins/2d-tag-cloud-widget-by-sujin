<?php
function sjInstall() {
	global $wpdb;
	global $sj_tag_db_version;
	
	$table_name = $wpdb->prefix . "terms_hit";
	
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
				term_id bigint(20) NOT NULL,
				hit bigint(20) DEFAULT 0 NOT NULL,
				UNIQUE KEY id(term_id)
				);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		add_option("sj_tag_db_version", $jal_db_version);
	}
}
register_activation_hook(__FILE__,'sjInstall');

# Save Count Data
function sjCountTagView($query) {
	if (is_single()) {
		global $wpdb, $wp_query;
		$table_name = $wpdb->prefix . "terms_hit";

		$tags = get_the_tags();

		// for another post type
		if (!$tags) {
			$tags = get_the_terms($wp_query->query_vars['p'], 'post_tag');
		}

		if ($tags) {
			foreach($tags as $tag) {
				if ($hit = $wpdb->get_var("SELECT hit FROM $table_name WHERE term_id = $tag->term_id")) {
					$hit++;
					$wpdb->update($table_name, array('hit' => $hit), array('term_id' => $tag->term_id));
				} else {
					$wpdb->insert($table_name, array('term_id' => $tag->term_id, 'hit' => 1));
				}
			}
		}
	}

	if (is_tag() && $query->is_main_query()) {
		global $wpdb;
		$table_name = $wpdb->prefix . "terms_hit";

		$term = get_queried_object();
		if (!$term) return;

		if ($hit = $wpdb->get_var("SELECT hit FROM $table_name WHERE term_id = $term->term_id")) {
			$hit++;
			$wpdb->update($table_name, array('hit' => $hit), array('term_id' => $term->term_id));
		} else {
			$wpdb->insert($table_name, array('term_id' => $term->term_id, 'hit' => 1));
		}
	}
}
add_action('parse_query', 'sjCountTagView');

function sj2DTagAdminEnqueueScripts() {
	wp_enqueue_script('jquery');
	
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_script('jquery-ui-button');
	wp_enqueue_script('jquery-ui-spinner');
	
	wp_enqueue_style('jquery-ui');
	wp_enqueue_script('iris'); 

	wp_enqueue_script('sujin_tag', plugin_dir_url( __FILE__ ) . '/assets/admin.js');
	wp_enqueue_style('sujin_tag', plugin_dir_url( __FILE__ ) . '/assets/admin.css');
}
add_action('admin_enqueue_scripts', 'sj2DTagAdminEnqueueScripts');

function sj2DTagEnqueue() {
	wp_enqueue_style('sujin_tag_front', get_bloginfo('wpurl') . '/?sj_tag_styles=1');
	
}
add_action('wp_enqueue_scripts', 'sj2DTagEnqueue');

// Generate Styles
// http://ottopress.com/2010/dont-include-wp-load-please/
add_filter('query_vars','sj_trigger_setting');
function sj_trigger_setting($vars) {
	$vars[] = 'sj_tag_styles';
	return $vars;
}

add_action('template_redirect', 'sj_trigger_check');
function sj_trigger_check() {
	if(intval(get_query_var('sj_tag_styles')) == 1) {
		header("Content-Type: text/css");
		$tag_config = get_option('sj_tag_conifg');

		# initialize;;
		$config = sjParseOptions($tag_config);
		$tag_config = $config['tag_config'];
		extract($config, EXTR_SKIP);

		foreach($tag_config['color'] as &$color) {
			if(!$color['bgcolor']) $color['bgcolor'] = 'transparent';
			$padding2 = $color['padding'] + 2;
			if ($color['padding']) $color['padding'] = $color['padding'] . 'px ' . $padding2 . 'px';
		}

		$style = 'margin-right:' . $margin_right . 'px !important; margin-bottom:' . $margin_bottom . 'px !important; display:inline-block !important; line-height:' . $line_height . $line_height_unit . ' !important; text-decoration:none !important;';

			?>
body .tag_cloud a {<?php echo $style ?>}
<?php
		
		for($i=1; $i<=$tag_step; $i++) {
			if (!empty($tag_config['color'][$i]['color']))
				$style_color = 'color:' . $tag_config['color'][$i]['color'] . ' !important;';

			if (!empty($tag_config['color'][$i]['bgcolor']))
				$style_color.= 'background-color:' . $tag_config['color'][$i]['bgcolor'] . ' !important;';

			if (!empty($tag_config['color'][$i]['radius']))
				$style_color.= 'border-radius:' . $tag_config['color'][$i]['radius'] . 'px !important;';

			if (!empty($tag_config['color'][$i]['padding']))
				$style_color.= 'padding:' . $tag_config['color'][$i]['padding'] . ' !important;';

			if (!empty($tag_config['size'][$i]))
				$style_size = 'font-size:' . $tag_config['size'][$i] . 'px !important;';

			?>
body .tag_cloud a.size_<?php echo $i?> {<?php echo $style_size ?>}
body .tag_cloud a.color_<?php echo $i?> {<?php echo $style_color ?>}
<?php
		}
	exit;
    }
}

function sjParseOptions($options) {
	if (!$options) {
		$tag_step = 1;

		$line_height = 1.3;
		$line_height_unit = 'em';
		$margin_right = 5;
		$margin_bottom = 10;

		$tag_config = array(
			'color' => array(
				1 => array(
					'color' => '#000000',
					'bgcolor' => '',
					'radius' => 0,
					'padding' => 0
				)
			),
			'size' => array(
				1 => 12
			)
		);

		$tag_method = 'click-color';
	} else {
		$tag_step = $options['tag_step'];
		$tag_method = $options['tag_method'];

		$line_height = $options['line_height'];
		$line_height_unit = $options['line_height_unit'];
		$margin_right = $options['margin_right'];
		$margin_bottom = $options['margin_bottom'];

		$tag_config = $options['tag_config'];
	}
	
	return array(
		'tag_step' => $tag_step,
		'tag_method' => $tag_method,
		'line_height' => $line_height,
		'line_height_unit' => $line_height_unit,
		'margin_right' => $margin_right,
		'margin_bottom' => $margin_bottom,
		'tag_config' => $tag_config
	);
}

# get tags
function sjGetTags($number, $separator, $sort) {
	global $wpdb;
	$tag_config = get_option('sj_tag_conifg');

	# initialize;;
	$config = sjParseOptions($tag_config);
	$tag_config = $config['tag_config'];
	extract($config, EXTR_SKIP);

	foreach($tag_config['color'] as &$color) {
		if(!$color['bgcolor']) $color['bgcolor'] = 'transparent';
		$padding2 = $color['padding'] + 2;
		if ($color['padding']) $color['padding'] = $color['padding'] . 'px ' . $padding2 . 'px';
	}

	$query_count = '
		SELECT
			terms.term_id as term_id,
			terms.name as tag_name,
			taxonomy.count as post_count,
			count.hit as post_hit

		FROM
			' . $wpdb->term_taxonomy . ' as taxonomy
				LEFT JOIN ' . $wpdb->terms . ' as terms ON taxonomy.term_id = terms.term_id
				LEFT JOIN ' . $wpdb->term_relationships . ' as relationship ON terms.term_id = relationship.term_taxonomy_id
				LEFT JOIN ' . $wpdb->posts . ' as post ON post.ID = relationship.object_ID
				LEFT JOIN ' . $wpdb->prefix . 'terms_hit as count ON count.term_id = terms.term_id

		WHERE
			taxonomy.taxonomy = "post_tag" AND count <> 0

		GROUP BY terms.term_id
		ORDER BY post_count DESC LIMIT ' . $number . '
	';

	$tags_count = $wpdb->get_results($query_count); // 포함수

	$query_hit = '
		SELECT
			terms.term_id as term_id,
			terms.name as tag_name,
			taxonomy.count as post_count,
			count.hit as post_hit

		FROM
			' . $wpdb->term_taxonomy . ' as taxonomy
				LEFT JOIN ' . $wpdb->terms . ' as terms ON taxonomy.term_id = terms.term_id
				LEFT JOIN ' . $wpdb->term_relationships . ' as relationship ON terms.term_id = relationship.term_taxonomy_id
				LEFT JOIN ' . $wpdb->posts . ' as post ON post.ID = relationship.object_ID
				LEFT JOIN ' . $wpdb->prefix . 'terms_hit as count ON count.term_id = terms.term_id

		WHERE
			taxonomy.taxonomy = "post_tag" AND count <> 0

		GROUP BY terms.term_id
		ORDER BY post_hit DESC LIMIT ' . $number . '
	';

	$tags_hit = $wpdb->get_results($query_hit); // 히트수

	$tags = array();

	// 히트와 뷰를 한 개씩 섞는다
	$k = 0;
	for ($i=0; $i<$number; $i++) {
		if (isset($tags_count[$i])) {
			$tags[$tags_count[$i]->term_id] = $tags_count[$i];
			if (!isset($tags[$tags_count[$i]->term_id])) {
				$k++;
				if ($k == $number) break;
			}
		}
		
		if ($sort == 'intersection') {
			$j = $number - $i;
		} else {
			$j = $i;
		}

		if (isset($tags_hit[$j])) {
			$tags[$tags_hit[$j]->term_id] = $tags_hit[$j];
			if (!isset($tags[$tags_hit[$j]->term_id])) {
				$k++;
				if ($k == $number) break;
			}
		}
	}

	# 한 자리에 몰아넣는 배열을 만든다, 민/맥스도 뽑고 각각의 녀석에게 스타일도 부여하기 위해
	$hit = $count = $tags_out = array();
	foreach ($tags as $tag) {
		$hit[$tag->term_id] = $tag->post_hit;
		$count[$tag->term_id] = $tag->post_count;
	}

	# 값으로 정렬
	asort($count);
	asort($hit);

	# 한 단계에 몇 개의 태그가 들어가는지...
	$tag_step = count($tags) / $tag_step;

	# 두바퀴만 더 돌려 카운트와 히트를 스텝에 맞는 값으로 변환한다
	$i = 0;
	$prev_value = -1;
	$prev_chanded = -1;

	foreach ($count as $key => &$value) {
		if ($prev_value == $value) {
			$value = $prev_value;
		} else {
			$prev_value = $value;
			$value = $prev_chanded = floor($i / $tag_step) + 1; // 0,1,2 대신 1,2,3을 사용했으니 편의상 +1
		}

		$i++;
	}

	$i = 0;
	$prev_value = -1;
	$prev_chanded = -1;

	foreach ($hit as $key => &$value) {
		if ($prev_value == $value) {
			$value = $prev_value;
		} else {
			$prev_value = $value;
			$value = $prev_chanded = floor($i / $tag_step) + 1; // 0,1,2 대신 1,2,3을 사용했으니 편의상 +1
		}

		$i++;
	}

	if ($sort == 'name') {
		$new_tag = array();

		foreach ($tags as $tag) {
			$new_tag[strtolower($tag->tag_name)] = $tag;
		}

		ksort($new_tag);
		$tags = $new_tag;
	}

	# 준비는 끝났다 +_+ 이제 녀석들을 만들어보자
	$i = 0;
	$tags_out = array();
	foreach ($tags as $tag) {
		$link = get_tag_link($tag->term_id);

		if ($tag_method == 'click-color') {
			$tag_size = $count[$tag->term_id];
			$tag_color = $hit[$tag->term_id] ? $hit[$tag->term_id] : 1;
		} else {
			$tag_color = $count[$tag->term_id];
			$tag_size = $hit[$tag->term_id] ? $hit[$tag->term_id] : 1;
		}

		$tags_out[] = '<a id="sj_tag_' . $i . '" class="size_' . $tag_size . ' color_' . $tag_color . '">' . $tag->tag_name . '</a>';
		$i++;
	}
	
	return implode($separator, $tags_out);
}