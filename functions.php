<?php
class sj2DTag {
	private $sj_tag_db_version = 1.0;
	private $table_name;

	/* Set */
	private $tag_set;
	private $set_number = 0;
	private $set_name = "Default Set";
	private $set_guid = "sj_tag_conifg";

	/* Config */
	private $number_of_tags;
	private $tag_separator;
	private $sort_by;

	private static $instance = false;

	protected function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . "terms_hit";

		$this->tag_set = get_option('sj_tag_set');
		$this->debug = get_option('sj_tag_debug');

		if ($this->debug) {
			@ini_set('display_errors', 1);
			@ini_set('error_reporting', 1);
		} else {
			@ini_set('display_errors', 0);
			@ini_set('error_reporting', 0);
		}

		if (!$this->tag_set) $this->tag_set = array(0 => 'Default Set');

		$this->trigger_hooks();

		$this->text_domain = "sujin-2d-tag-cloud";

	}

	private function redirect($url) {
		if (!$url) $url = $_SERVER['HTTP_REFERER'];

		if (function_exists("wp_redirect")) {
			wp_redirect($url);
			die;
		}

		echo '<script>window.location="' . $url . '"</script>';
		die;
	}

	public function trigger_hooks() {
		# hooks
		register_activation_hook( __DIR__.'/two-dimensional-tag-cloud-sujin.php', array(&$this, 'activate_plgin'));
		add_action('parse_query', array(&$this, 'increase_tag_view_count'));
		add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
		add_action('admin_menu', array(&$this, 'trigget_admin_menu'));
		add_action('plugins_loaded', array(&$this, 'load_plugin_textdomain'));
	}

	# Activation Hook & Make Database
	# 구동 시 훅을 걸어 DB를 생성합니다.
	public function activate_plgin() {
		global $wpdb;

		if($wpdb->get_var("show tables like '$this->table_name'") != $this->table_name) {
			$this->make_table();
		}
	}

	private function make_table() {
		$sql = "CREATE TABLE $this->table_name (
				term_id bigint(20) NOT NULL,
				hit bigint(20) DEFAULT 0 NOT NULL,
				UNIQUE KEY id(term_id)
				);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$error = dbDelta($sql);

		add_option("sj_tag_db_version", $this->sj_tag_db_version);
	}

	private function check_table_exist() {
		global $wpdb;

		if($wpdb->get_var("show tables like '$this->table_name'") != $this->table_name) {
			$this->make_table();
		}
	}

	# When visitors click the post or tag
	# 내가 만든 DB에 데이터를 쑤셔 넣으라우!
	public function increase_tag_view_count($query) {
		if (is_single() && $query->is_single) {
			$this->increase_tag_view_count_of_single_post($query->query_vars['p']);
		}

		if (is_tag() && $query->query_vars['tag']) {
			$term = get_term_by('slug', $query->query_vars['tag'], 'post_tag');

			if ($term) {
				$this->increase_tag_view_count_of_tag($term->term_id);
			}
		}
	}

	private function increase_tag_view_count_of_single_post($post_id) {
		$tags = get_the_tags($post_id);

		# For another post type
		# 포스트 톼잎이 틀리면 get_the_tags로 가져올 수 없더라
		if (!$tags) {
			global $wp_query;
			$tags = get_the_terms($wp_query->query_vars['p'], 'post_tag');
		}

		if ($tags) {
			foreach($tags as $tag) {
				$this->increase_tag_view_count_of_tag($tag->term_id);
			}
		}
	}

	private function increase_tag_view_count_of_tag($tag_id) {
		if ($this->debug) {
			$this->check_table_exist();
		}

		global $wpdb;

		if ($hit = $wpdb->get_var("SELECT hit FROM $this->table_name WHERE term_id = $tag_id")) {
			$hit++;
			$wpdb->update($this->table_name, array('hit' => $hit), array('term_id' => $tag_id));
		} else {
			$wpdb->insert($this->table_name, array('term_id' => $tag_id, 'hit' => 1));
		}
	}

	public function load_plugin_textdomain() {
		$lang_dir = basename(dirname(__FILE__)) . '/languages';
		load_plugin_textdomain($this->text_domain, 'wp-content/plugins/' . $lang_dir, $lang_dir);
	}

	# 태그클라우드를 뽑아요!
	public function get_tag_cloud() {
		if ($this->debug) {
			$this->check_table_exist();
		}

		global $wpdb;

		$tag_config = get_option($this->set_guid);

		# initialize;
		$config = $this->parse_config($tag_config);
		$tag_config = $config['tag_config'];
		extract($config, EXTR_SKIP);
		$tag_config['color'] = $this->convert_css_style($tag_config['color']);

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
					LEFT JOIN ' . $this->table_name . ' as count ON count.term_id = terms.term_id

			WHERE
				taxonomy.taxonomy = "post_tag" AND count <> 0

			GROUP BY terms.term_id
			ORDER BY post_count DESC LIMIT ' . $this->number_of_tags . '
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
					LEFT JOIN ' . $this->table_name . ' as count ON count.term_id = terms.term_id

			WHERE
				taxonomy.taxonomy = "post_tag" AND count <> 0

			GROUP BY terms.term_id
			ORDER BY post_hit DESC LIMIT ' . $this->number_of_tags . '
		';

		$tags_hit = $wpdb->get_results($query_hit); // 히트수

		$tags = array();

		// 히트와 뷰를 한 개씩 섞는다
		$k = 0;
		for ($i=0; $i<$this->number_of_tags; $i++) {
			if (isset($tags_count[$i])) {
				$tags[$tags_count[$i]->term_id] = $tags_count[$i];
				if (!isset($tags[$tags_count[$i]->term_id])) {
					$k++;
					if ($k == $this->number_of_tags) break;
				}
			}

			if ($this->sort_by == 'intersection') {
				$j = $this->number_of_tags - $i;
			} else {
				$j = $i;
			}

			if (isset($tags_hit[$j])) {
				$tags[$tags_hit[$j]->term_id] = $tags_hit[$j];
				if (!isset($tags[$tags_hit[$j]->term_id])) {
					$k++;
					if ($k == $this->number_of_tags) break;
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
				$value = $prev_chanded;
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
				$value = $prev_chanded;
			} else {
				$prev_value = $value;
				$value = $prev_chanded = floor($i / $tag_step) + 1; // 0,1,2 대신 1,2,3을 사용했으니 편의상 +1
			}

			$i++;
		}

		if ($this->sort_by == 'name') {
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

			$tags_out[] = '<a id="sj_tag_' . $i . '" class="size_' . $tag_size . ' color_' . $tag_color . '" href="' . $link . '">' . $tag->tag_name . '</a>';
			$i++;
		}

		return '<div class="tag_cloud sj_tagcloud_set_' . $this->set_number . '">' . implode($this->tag_separator, $tags_out) . '</div><style>' . $this->print_css() . '</style>';
	}

	public function admin_enqueue_scripts() {
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

	public function print_css() {
		$tag_config = get_option($this->set_guid);

		# initialize;
		$config = $this->parse_config($tag_config);
		$tag_config = $config['tag_config'];
		extract($config, EXTR_SKIP);
		$tag_config['color'] = $this->convert_css_style($tag_config['color']);

		$style = 'margin-right:' . $margin_right . 'px !important; margin-bottom:' . $margin_bottom . 'px !important; display:inline-block !important; line-height:' . $line_height . $line_height_unit . ' !important; text-decoration:none !important;';

		$underline = ($underline) ? 'text-decoration:underline !important;' : '';

		$output = 'body .sj_tagcloud_set_' . $this->set_number . ' a {' . $style . '}';

		for($i=1; $i<=$tag_step; $i++) {
			$style_color = '';
			$style_size = '';
			$style_color_over = '';

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

			if (!empty($tag_config['color'][$i]['color_over']))
				$style_color_over = 'color:' . $tag_config['color'][$i]['color_over'] . ' !important;';

			if (!empty($tag_config['color'][$i]['bgcolor_over']))
				$style_color_over.= 'background-color:' . $tag_config['color'][$i]['bgcolor_over'] . ' !important;';

			$output.= 'body .sj_tagcloud_set_' . $this->set_number . ' a.size_' . $i . ' {' . $style_size . '}';
			$output.= 'body .sj_tagcloud_set_' . $this->set_number . ' a.color_' . $i . ' {' . $style_color . ' transition:color 0.25s ease-in 0s, background-color 0.25s ease-in 0s;}';
			$output.= 'body .sj_tagcloud_set_' . $this->set_number . ' a.color_' . $i . ':hover {' . $style_color_over . ' ' . $underline . '}';
		}

		return $output;
	}

	private function convert_css_style($options) {
		foreach($options as &$color) {
			if(!$color['bgcolor']) $color['bgcolor'] = 'transparent';
			$padding2 = $color['padding'] + 2;
			if ($color['padding']) $color['padding'] = $color['padding'] . 'px ' . $padding2 . 'px';
		}

		return $options;
	}

	private function parse_config($options) {
		# Default Option
		if (!$options) {
			$tag_step = 1;

			$line_height = 1.3;
			$line_height_unit = 'em';
			$margin_right = 5;
			$margin_bottom = 10;
			$underline = 0;

			$tag_config = array(
				'color' => array(
					1 => array(
						'color' => '#000000',
						'bgcolor' => '',
						'color_over' => '',
						'bgcolor_over' => '',
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
			$underline = $options['underline'];

			$tag_config = $options['tag_config'];
		}

		$this->debug = get_option('sj_tag_debug');

		return array(
			'tag_step' => $tag_step,
			'tag_method' => $tag_method,
			'line_height' => $line_height,
			'line_height_unit' => $line_height_unit,
			'margin_right' => $margin_right,
			'margin_bottom' => $margin_bottom,
			'underline' => $underline,
			'tag_config' => $tag_config
		);
	}

	public function trigget_admin_menu() {
		add_options_page(__('2D Tag Cloud', $this->text_domain), __('2D Tag Cloud', $this->text_domain), 'manage_options', '2D-tag-cloud-options', array(&$this, 'admin_menu'));
	}

	public function admin_menu() {
		if (isset($_POST['action']) && check_admin_referer($this->text_domain)) {
			switch($_POST['action']) {
				case 'update' :
					if (!$_POST['set_current_id']) {
						$this->set_by_number(0);
					} else {
						$this->set_by_number($_POST['set_current_id']);
					}

					$this->save_option();
					break;

				case 'makenew' :
					$this->make_new_option();
					break;

				case 'delete' :
					$this->delete_option();
					break;
			}
		}

		if (!empty($_GET['set']))
			$this->set_by_number($_GET['set']);

		$this->print_admin_page();
	}

	public function save_option() {
		$tag_config = array();

		for($i=1; $i<$_POST['tag_step']+1; $i++) {
			$tag_config['color'][$i] = array(
				'color' => $_POST['tag_color_step_' . $i],
				'bgcolor' => $_POST['tag_bgcolor_step_' . $i],
				'color_over' => $_POST['tag_color_over_step_' . $i],
				'bgcolor_over' => $_POST['tag_bgcolor_over_step_' . $i],
				'radius' => $_POST['tag_radius_step_' . $i],
				'padding' => $_POST['tag_padding_step_' . $i]
			);

			$tag_config['size'][$i] =$_POST['tag_size_step_' . $i];
		}

		$underline = (isset($_POST['underline'])) ? 'true' : '';

		$tag_config = array(
			'tag_step' => $_POST['tag_step'],
			'tag_method' => $_POST['tag_method'],
			'setting_method' => $_POST['setting_method'],
			'line_height' => $_POST['line_height'],
			'line_height_unit' => $_POST['line_height_unit'],
			'margin_right' => $_POST['margin_right'],
			'margin_bottom' => $_POST['margin_bottom'],
			'underline' => $underline,
			'tag_config' => $tag_config
		);

		update_option($this->set_guid, $tag_config);

		$debug = (isset($_POST['debug'])) ? 'true' : '';
		update_option('sj_tag_debug', $debug);
	}

	private function make_new_option() {
		if (empty($_POST['set_name'])) {
			$this->redirect(get_site_url() . '/wp-admin/options-general.php?page=2D-tag-cloud-options');
		}

		$this->set_name = $_POST['set_name'];
		$this->tag_set[] = $this->set_name;
		$this->set_tag_set();

		end($this->tag_set);
		$this->set_by_number(key($this->tag_set));

		$this->save_option();

		$this->redirect(get_site_url() . '/wp-admin/options-general.php?page=2D-tag-cloud-options&set=' . $this->set_number);
	}

	private function delete_option() {
		if (empty($_POST['set_current_id'])) {
			$this->redirect(get_site_url() . '/wp-admin/options-general.php?page=2D-tag-cloud-options');
		}

		unset($this->tag_set[$_POST['set_current_id']]);
		$this->set_tag_set();
		$this->set_by_number($_POST['set_current_id']);
		delete_option($this->set_guid);

		$this->redirect(get_site_url() . '/wp-admin/options-general.php?page=2D-tag-cloud-options');
	}

	private function set_tag_set() {
		update_option('sj_tag_set', $this->tag_set);
	}

	public function set_by_number($number) {
		$this->set_number = isset($number) ? $number : 0;
		$this->set_guid = ($number != 0) ? 'sj_tag_conifg_' . $number : 'sj_tag_conifg';
	}

	public function set_by_name($name) {
		foreach($this->tag_set as $key => $value) {
			if ($value == $name) {
				$this->set_by_number($key);
				break;
			}
		}
	}

	public function set_cloud_option($number, $separator, $sort) {
		$this->number_of_tags = $number;
		$this->tag_separator = $separator;
		$this->sort_by = $sort;
	}

	private function print_admin_page() {
		$tag_config = get_option($this->set_guid);
		$config = $this->parse_config($tag_config);

		$tag_config = $config['tag_config'];
		extract($config);

		?>

		<div class="wrap sjTag">
			<div class="icon32" id="icon-options-general"><br></div><h2><?php _e('2D Tag Cloud Setting', $this->text_domain); ?></h2>

			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="donation">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>

			<form method="post" id="sjTagForm">
				<input type="hidden" value="2D-tag-cloud-options" name="option_page">
				<input type="hidden" value="update" name="action">
				<input type="hidden" value="<?php echo $this->set_number ?>" name="set_current_id">
				<input type="hidden" value="<?php echo $this->set_name ?>" name="set_current_name">

				<input type="hidden" value="<?php _e('Step', $this->text_domain); ?>" id="text_of_step">
				<input type="hidden" value="<?php _e('You cannot delete the default set.', $this->text_domain); ?>" id="text_of_delete_alert">
				<input type="hidden" value="<?php _e('Do you really want to delete this set?', $this->text_domain); ?>" id="text_of_delete_confirm">
				<input type="hidden" value="<?php _e('You must fill a set name.', $this->text_domain); ?>" id="text_of_make_alert">

				<?php wp_nonce_field($this->text_domain) ?>

				<div class="col_wrapper">
					<label for="tag_set"><?php _e('Set', $this->text_domain); ?></label>
					<select id="tag_set" name="tag_set">

						<?php foreach($this->tag_set as $key=>$value) { ?>

						<option value="<?php echo $key ?>" <?php if ($key == $this->set_number) echo 'selected="selected"' ?>><?php echo $value ?></option>

						<?php } ?>

					</select>

					<script>
					jQuery('#tag_set').bind('change', function() {
						window.location = window.location.pathname + '?page=2D-tag-cloud-options&set=' + jQuery(this).val();
					});
					</script>

					<a href="#" onclick="delete_set(<?php echo $this->set_number ?>); return false;" class="button"><?php _e('Delete this set', $this->text_domain); ?></a>
					<input id="set_name" name="set_name" />
					<a href="#" onclick="make_set(); return false;" class="button"><?php _e('Make new set', $this->text_domain); ?></a>
					<p class="desc label"><?php _e('You cannot delete default set. If you delete the set, which is used by widget, it will be shown as default set.', $this->text_domain); ?></p>
				</div>

				<div class="col_wrapper">
					<label for="tag_step"><?php _e('Tag Step', $this->text_domain); ?></label>
					<input id="tag_step" class="jquery-spinner" name="tag_step" value="<?php echo $tag_step ?>" />
					<p class="desc label"></p>
				</div>

				<div class="col_wrapper">
					<label for="tag_method"><?php _e('Output', $this->text_domain); ?></label>
					<select id="tag_method" name="tag_method">
						<option value="click-color" <?php if ($tag_method == 'click-color') echo 'selected="selected"' ?>><?php _e('Color:View / Size:Including', $this->text_domain); ?></option>
						<option value="include-color" <?php if ($tag_method == 'include-color') echo 'selected="selected"' ?>><?php _e('Color:Including / Size:View', $this->text_domain); ?></option>
					</select>
					<p class="desc label"></p>
				</div>

				<div class="col_wrapper">
					<label><?php _e('Preset', $this->text_domain); ?></label>
					<a href="#" onclick="do_preset_4_white(); return false;"><?php _e('4 Step / Bright Background', $this->text_domain); ?></a><br />
					<a href="#" onclick="do_preset_4_black(); return false;"><?php _e('4 Step / Dark Background', $this->text_domain); ?></a>
					<p class="desc label"></p>
				</div>

				<div class="col_wrapper">
					<label for="line_height"><?php _e('Line Height', $this->text_domain); ?></label>
					<input id="line_height" class="" name="line_height" value="<?php echo $line_height ?>" />
					<select id="line_height_unit" name="line_height_unit">
						<option value="em" <?php if ($line_height_unit == 'em') echo 'selected="selected"' ?>>em</option>
						<option value="px" <?php if ($line_height_unit == 'px') echo 'selected="selected"' ?>>px</option>
					</select>
					<p class="desc label"></p>
				</div>

				<div class="col_wrapper">
					<label for="margin_right"><?php _e('Right Margin', $this->text_domain); ?></label>
					<input id="margin_right" class="jquery-spinner" name="margin_right" value="<?php echo $margin_right ?>" />
					<p class="desc label"></p>
				</div>

				<div class="col_wrapper">
					<label for="margin_bottom"><?php _e('Bottom Margin', $this->text_domain); ?></label>
					<input id="margin_bottom" class="jquery-spinner" name="margin_bottom" value="<?php echo $margin_bottom ?>" />
					<p class="desc label"></p>
				</div>

				<div class="col_wrapper">
					<label for=""><?php _e('Underline', $this->text_domain); ?></label>
					<input type="checkbox" id="underline" name="underline" <?php if ($underline) echo 'checked="checked"' ?> /> <label for="underline" id="label_underline"><?php _e('Check if show underline when mouse-over', $this->text_domain); ?></label>
				</div>

				<div class="col_wrapper">
					<label for=""><?php _e('Turn on Debug Mode', $this->text_domain); ?></label>
					<input type="checkbox" id="debug" name="debug" <?php if ($this->debug) echo 'checked="checked"' ?> /> <label for="debug" id="label_debug"><?php _e('Check if you have a problem, and I wish you send me a message of your footer to sujin.2f@gmail.com', $this->text_domain); ?></label>
				</div>

				<div id="prev_wrapper">
					<table id="sjTagTable" class="wp-list-table widefat fixed posts">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<?php foreach($tag_config['color'] as $key => $value) { ?>
								<th id="tag_step_<?php echo $key ?>_preview"><span><?php _e('Step', $this->text_domain); ?> <?php echo $key ?></span></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th><?php _e('Text Color', $this->text_domain); ?></th>
								<?php foreach($tag_config['color'] as $key => $value) { ?>
								<td><input type="text" id="tag_color_step_<?php echo $key ?>" name="tag_color_step_<?php echo $key ?>" class="tag_color color-picker" value="<?php echo $value['color'] ?>" /></td>
								<?php } ?>
							</tr>

							<tr>
								<th><?php _e('Background Color', $this->text_domain); ?></th>
								<?php foreach($tag_config['color'] as $key => $value) { ?>
								<td><input type="text" id="tag_bgcolor_step_<?php echo $key ?>" name="tag_bgcolor_step_<?php echo $key ?>" class="tag_bgcolor color-picker" value="<?php echo $value['bgcolor'] ?>" /></td>
								<?php } ?>
							</tr>

							<tr>
								<th><?php _e('Border Radius', $this->text_domain); ?></th>
								<?php foreach($tag_config['color'] as $key => $value) { ?>
								<td><input type="text" id="tag_radius_step_<?php echo $key ?>" name="tag_radius_step_<?php echo $key ?>" class="tag_radius jquery-spinner" value="<?php echo $value['radius'] ?>" /></td>
								<?php } ?>
							</tr>

							<tr>
								<th><?php _e('Padding', $this->text_domain); ?></th>
								<?php foreach($tag_config['color'] as $key => $value) { ?>
								<td><input type="text" id="tag_padding_step_<?php echo $key ?>" name="tag_padding_step_<?php echo $key ?>" class="tag_padding jquery-spinner" value="<?php echo $value['padding'] ?>" /></td>
								<?php } ?>
							</tr>

							<tr>
								<th><?php _e('Size', $this->text_domain); ?></th>
								<?php foreach($tag_config['size'] as $key => $value) { ?>
								<td><input type="text" id="tag_size_step_<?php echo $key ?>" name="tag_size_step_<?php echo $key ?>" class="tag_size jquery-spinner" value="<?php echo $value ?>" /></td>
								<?php } ?>
							</tr>

								<th><?php _e('Text Color', $this->text_domain); ?> <?php _e('(Over)', $this->text_domain); ?></th>
								<?php foreach($tag_config['color'] as $key => $value) { ?>
								<td><input type="text" id="tag_color_over_step_<?php echo $key ?>" name="tag_color_over_step_<?php echo $key ?>" class="tag_color_over color-picker" value="<?php echo $value['color_over'] ?>" /></td>
								<?php } ?>
							</tr>

							<tr>
								<th><?php _e('Background Color', $this->text_domain); ?> <?php _e('(Over)', $this->text_domain); ?></th>
								<?php foreach($tag_config['color'] as $key => $value) { ?>
								<td><input type="text" id="tag_bgcolor_over_step_<?php echo $key ?>" name="tag_bgcolor_over_step_<?php echo $key ?>" class="tag_bgcolor_over color-picker" value="<?php echo $value['bgcolor_over'] ?>" /></td>
								<?php } ?>
							</tr>
						</tbody>
					</table>

					<h3 id="sjTagH3Preview"><?php _e('Preview', $this->text_domain); ?> <a href="#" onclick="sjSetPreview(); return false;" class="button"><?php _e('Make Preview', $this->text_domain); ?></a></h3>
					<div id="sjTagPreview">
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Tag</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Cloud</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Wordpress</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">API</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">PHP</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">CMS</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Linux</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">한국어</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">English</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">日本語</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">le français</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Community</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Europe</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">North and South America</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Asia</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Africa</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Oceania</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Information</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Languages</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Italy</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Canada</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Haiti</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Brazil</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Egypt</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Southeast</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Lebanon</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Situation</a>,
						<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Phonology</a>,
					</div>
				</div>

				<p><?php printf(__('You can use a shortcode also please see <a href="http://wordpress.org/extend/plugins/2d-tag-cloud-widget-by-sujin/other_notes/" target="_blank">%s</a>', $this->text_domain), __('this page', $this->text_domain)); ?></p>

				<p class="submit">
					<input type="submit" value="<?php _e('Save Changes', $this->text_domain); ?>" class="button button-primary" id="submit" name="submit">
					<a href="<?php echo $_SERVER['REQUEST_URI'] ?>" class="button"><?php _e('Cancel', $this->text_domain); ?></a>
				</p>
			</form>
		</div>
		<?php
	}

	public static function getInstance() {
		if (!self::$instance)
			self::$instance = new self;

		return self::$instance;
	}
}

$sj2DTag = sj2DTag::getInstance();