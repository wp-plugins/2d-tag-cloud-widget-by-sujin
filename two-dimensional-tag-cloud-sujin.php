<?php
/**
 * Plugin Name: 2D Tag Cloud by Sujin
 * Plugin URI: http://www.sujinc.com/2d-tag-cloud-widget/
 * Description: Generate 2-Dimentional Tag Cloud. For example, size representative hit count and color representative the count that tag includes.
 * Version: 1.0
 * Author: Sujin Choi
 * Author URI: http://www.sujinc.com/
 * License: GPLv2 or later
 * Text Domain: tag-cloud-widget-sujin
 */

global $sj_tag_db_version;
$sj_tag_db_version = "1.0";

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

class SJ_Widget_TagCloud extends WP_Widget {
	public $widget_id = 'tag_cloud_widget_sujin';
	public $widget_name;
	public $widget_title;

	function __construct() {
		$this->widget_id = 'tag_cloud_widget_sujin';
		$this->widget_name = '2D Tag Cloud Widget by Sujin';
		$this->widget_title = '2D Tag Cloud Widget by Sujin';

		$widget_ops = array(
			'classname' => $this->widget_id,
			'description' =>'Generate 2-Dimentional Tag Cloud'
		);

		$control_ops = array(
			'width' => 500,
		);

		parent::__construct($this->widget_id, $this->widget_name, $widget_ops, $control_ops);
		$this->alt_option_name = 'widget_'.$this->id_base;
	}

	function widget($args, $instance) {
		global $wpdb;

		extract($args, EXTR_SKIP);

		$number = isset($instance['number']) ? $instance['number'] : 20;
		$title = isset($instance['title']) ? $instance['title'] : '';
		$separator = isset($instance['separator']) ? $instance['separator'] : '';

		echo $before_widget;
		echo $before_title . apply_filters('widget_title', $title) . $after_title;

		$tag_config = get_option('sj_tag_conifg');

		# initialize;;
		if (!$tag_config) {
			$tag_step = 1;
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
			$tag_step = $tag_config['tag_step'];
			$tag_method = $tag_config['tag_method'];
			$tag_config = $tag_config['tag_config'];
		}

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


			if (isset($tags_hit[$i])) {
				$tags[$tags_hit[$i]->term_id] = $tags_hit[$i];
				if (!isset($tags[$tags_hit[$i]->term_id])) {
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

		# 준비는 끝났다 +_+ 이제 녀석들을 만들어보자
		foreach ($tags as $tag) {
			$link = get_tag_link($tag->term_id);

			if ($tag_method == 'click-color') {
				$tag_size = $count[$tag->term_id];
				$tag_color = $hit[$tag->term_id];
			} else {
				$tag_color = $count[$tag->term_id];
				$tag_size = $hit[$tag->term_id];
			}

			$style = 'color:' . $tag_config['color'][$tag_color]['color'] . ';';
			$style.= 'background-color:' . $tag_config['color'][$tag_color]['bgcolor'] . ';';
			$style.= 'border-radius:' . $tag_config['color'][$tag_color]['radius'] . 'px;';
			$style.= 'padding:' . $tag_config['color'][$tag_color]['padding'] . ';';
			$style.= 'font-size:' . $tag_config['size'][$tag_size] . 'px;';
			$style.= 'margin-right:5px;';

			$tags_out[] = '<a href="' . $link . '" style="' . $style . '">' . $tag->tag_name . '</a>';
		}

		echo '<div class="tag_cloud">' . implode($separator, $tags_out) . '</div>';
		echo $after_widget;
	} // function widget($args, $instance)

	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		$instance['number'] = $new_instance['number'];
		$instance['title'] = $new_instance['title'];
		$instance['separator'] = $new_instance['separator'];

		return $instance;
	} // function update($new_instance, $old_instance)

	function form($instance) {
		$number = isset($instance['number']) ? $instance['number'] : 20;
		$title = isset($instance['title']) ? $instance['title'] : '';
		$separator = isset($instance['separator']) ? $instance['separator'] : '';

		?>

			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>">Number of tags to show</label>
				<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('separator'); ?>">Separator</label>
				<input class="" id="<?php echo $this->get_field_id('separator'); ?>" name="<?php echo $this->get_field_name('separator'); ?>" type="text" value="<?php echo $separator; ?>" />
			</p>

		<?php
	} // function form($instance)
}

# Activate the Widget
function sjActivateWidgetTagCloud() {
	register_widget('SJ_Widget_TagCloud');
}
add_action('widgets_init', 'sjActivateWidgetTagCloud');

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

# Admin Page
function sj2DTagAddSettingPage() {
	add_options_page('2D Tag Cloud', '2D Tag Cloud', 'manage_options', '2D-tag-cloud-options', 'sj2DTagSetting');
}

function sj2DTagSetting() {
	if ($_POST['submit'] == 'Save Changes') {
		$tag_step = $_POST['tag_step'];
		$tag_method = $_POST['tag_method'];
		$tag_config = array();

		for($i = 1; $i < $_POST['tag_step']+1; $i++) {
			$tag_config['color'][$i] = array(
				'color' => $_POST['tag_color_step_' . $i],
				'bgcolor' => $_POST['tag_bgcolor_step_' . $i],
				'radius' => $_POST['tag_radius_step_' . $i],
				'padding' => $_POST['tag_padding_step_' . $i]
			);

			$tag_config['size'][$i] =$_POST['tag_size_step_' . $i];
		}
		
		$tag_config = array(
			'tag_step' => $tag_step,
			'tag_method' => $tag_method,
			'tag_config' => $tag_config,
		);
		update_option('sj_tag_conifg', $tag_config);
	}

	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_script('jquery-ui-button');
	wp_enqueue_script('jquery-ui-spinner');
	
	wp_enqueue_style('jquery-ui');
	//wp_enqueue_style('wp-jquery-ui-button');
//	wp_enqueue_style('wp-jquery-ui-spinner');
//	wp_enqueue_style('wp-jquery-ui-dialog');
	
	wp_enqueue_script('iris'); 

	wp_enqueue_script('sujin_tag', plugin_dir_url( __FILE__ ) . '/assets/admin.js');
	wp_enqueue_style('sujin_tag', plugin_dir_url( __FILE__ ) . '/assets/admin.css');

	$tag_config = get_option('sj_tag_conifg');

	# initialize;;
	if (!$tag_config) {
		$tag_step = 1;
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
		$tag_step = $tag_config['tag_step'];
		$tag_method = $tag_config['tag_method'];
		$tag_config = $tag_config['tag_config'];
	}

	?>

	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div><h2>2D Tag Cloud</h2>
	
		<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
			<h2>General</h2>
			<div class="col_wrapper">
				<label for="tag_step">Tag Step</label>
				<input id="tag_step" class="jquery-spinner" name="tag_step" value="<?php echo $tag_step ?>" />
				<p class="desc label"></p>
			</div>
	
			<div class="col_wrapper">
				<label for="tag_method">Method</label>
				<select id="tag_method" name="tag_method">
					<option value="click-color" <?php if ($tag_method == 'click-color') echo 'selected="selected"' ?>>Color:View / Size:Including</option>
					<option value="include-color" <?php if ($tag_method == 'include-color') echo 'selected="selected"' ?>>Color:Including / Size:View</option>
				</select>
				<p class="desc label"></p>
			</div>

			<div class="col_wrapper">
				<label for="tag_method">Preset</label>
				<a href="#" onclick="do_preset_4_white(); return false;">4 Step / White Background</a>
				<p class="desc label"></p>
			</div>

			<div id="prev_wrapper">
				<h2>Color</h2>
				<ul id="tag_color_step">
					<?php foreach($tag_config['color'] as $key => $value) { ?>
					<li id="tag_color_step_<?php echo $key ?>_preview">
						<span>Step <?php echo $key ?></span>
						<input type="hidden" id="tag_color_step_<?php echo $key ?>" name="tag_color_step_<?php echo $key ?>" class="tag_color" value="<?php echo $value['color'] ?>" />
						<input type="hidden" id="tag_bgcolor_step_<?php echo $key ?>" name="tag_bgcolor_step_<?php echo $key ?>" class="tag_bgcolor" value="<?php echo $value['bgcolor'] ?>" />
						<input type="hidden" id="tag_radius_step_<?php echo $key ?>" name="tag_radius_step_<?php echo $key ?>" class="tag_radius" value="<?php echo $value['radius'] ?>" />
						<input type="hidden" id="tag_padding_step_<?php echo $key ?>" name="tag_padding_step_<?php echo $key ?>" class="tag_padding" value="<?php echo $value['padding'] ?>" />
					</li>
					<?php } ?>
				</ul>
	
				<div id="color_selector" class="modal">
					<div id="color_wrapper">
						<div class="col_wrapper">
							<label for="tag_color">Color</label>
							<input id="tag_color" class="color-picker font-color" name="tag_color" />
						</div>
			
						<div class="col_wrapper">
							<label for="tag_bgcolor">Background Color</label>
							<button id="tag_bgcolor_cancel" class="tag_bgcolor_cancel">Transparent</button>
							<input id="tag_bgcolor" class="color-picker bg-color" name="tag_bgcolor" />
						</div>
					</div>
		
					<div class="col_wrapper">
						<label for="tag_radius">Border Radius</label>
						<input id="tag_radius" class="border_radius jquery-spinner" /> px
					</div>
		
					<div class="col_wrapper">
						<label for="tag_padding">Padding</label>
						<input id="tag_padding" class="padding jquery-spinner" /> px
					</div>
				</div>
	
				<h2>Size</h2>
				<ul id="tag_size_step">
					<?php foreach($tag_config['size'] as $key => $value) { ?>
					<li id="tag_size_step_<?php echo $key ?>_preview">
						<span>Step <?php echo $key ?></span>
						<input type="hidden" id="tag_size_step_<?php echo $key ?>" name="tag_size_step_<?php echo $key ?>" class="tag_size" value="<?php echo $value ?>" />
					</li>
					<?php } ?>
				</ul>
	
				<div id="size_selector" class="modal">
					<div class="col_wrapper">
						<label for="tag_size">Size</label>
						<input id="tag_size" class="jquery-spinner tag_size" /> px
					</div>
				</div>
			</div>

			<p class="submit">
				<input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit">
				<a href="<?php echo $_SERVER['REQUEST_URI'] ?>" class="button">Cancel</a>
			</p>
		</form>

		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHLwYJKoZIhvcNAQcEoIIHIDCCBxwCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYCI0X2o5NDGf1zzBqMgJbybEzgey5TmWKLnsWCcm7R9sYxHFFsbeDUL4VSvelZE74tGIHUllp/IFT7BKr2zK4tVVK+h9YvWGFRaJJxEdO90pY5J/dRx8L5Cqd3+SAQeS0OQeJ0Mh+Xk+nPtRjxmRfUe3zjL3aPtTzGj2spAfSInIjELMAkGBSsOAwIaBQAwgawGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIvCDCcxHI/GmAgYgvNyr9N8jf59rPYi9VqGvpI+2hIGVOPfQHaYiXumBkSltIqrzHlgOLw2or6DTlbeDrqtzwqCWS3MD2yvPdOmhaOKNhxsyksmnhzbNs5u62GGbYPQB9Wv+srPtsXSTP8az2etFNJZ9SUVj+u1h1ItW1Ix1NVlbly+8LZjemnIobjSMeWHmrlvcDoIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMTMwMTA2MTQyMjE3WjAjBgkqhkiG9w0BCQQxFgQUvTPrqEKlOAYDniaD8HDWMC6C8VEwDQYJKoZIhvcNAQEBBQAEgYBQglRLsBVFjwreid5pjCnBlCjct3UlYJIieAsviTQ5Jg3QpTNysJSvy1OrUTTcZE6z/nfSubJMCiNOQ9O7B3bXPqi9IaMnWPYrwpyAMbPATx5MelaHsAVBef5WU/s7eJMHQXEu8BKVtEj+HiPGj54s04DlYtxkSvGAOH/OYq8Ybw==-----END PKCS7-----">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>

	</div>
	<?php
}
add_action('admin_menu', 'sj2DTagAddSettingPage');
