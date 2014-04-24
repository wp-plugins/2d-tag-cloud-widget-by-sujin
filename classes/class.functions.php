<?php
class SJ2DTAG_functions {
	public static $table_name = 'terms_hit';
	public static $text_domain = 'sujin-2d-tag-cloud';
	public static $default_option = array(
		'title' => '',
		'tag_method' => 'click-color',
		'line_height' => 24,
		'line_height_unit' => 'px',
		'margin_right' => 5,
		'margin_bottom' => 6,
		'underline' => 0,
		'tag_config' => array(
			1 => array(
				'color' => '#CECECE',
				'bgcolor' => '',
				'color_over' => '',
				'bgcolor_over' => '',
				'radius' => 0,
				'padding' => 0,
				'size' => 12
			),
			2 => array(
				'color' => '#856797',
				'bgcolor' => '',
				'color_over' => '',
				'bgcolor_over' => '',
				'radius' => 0,
				'padding' => 0,
				'size' => 16
			),
			3 => array(
				'color' => '#FFFFFF',
				'bgcolor' => '#C9BBD2',
				'color_over' => '',
				'bgcolor_over' => '',
				'radius' => 5,
				'padding' => 3,
				'size' => 21
			),
			4 => array(
				'color' => '#FFFFFF',
				'bgcolor' => '#7629A3',
				'color_over' => '',
				'bgcolor_over' => '',
				'radius' => 5,
				'padding' => 3,
				'size' => 26
			)
		)
	);

	# 액티베이션 훅
	public static function activate_plugin() {
		$is_table = self::is_table_exists();
		if ( empty( $is_table ) ) {
			self::create_table();
		}
	}

	# 테이블이 존재하는지 검사
	protected static function is_table_exists() {
		global $wpdb;
		$table_name = self::$table_name;

		return $wpdb->query( "show tables like '{$wpdb->prefix}{$table_name}';" );
	}

	# 테이블 생성
	protected static function create_table() {
		global $wpdb;
		$table_name = self::$table_name;

		$sql = "CREATE TABLE {$wpdb->prefix}{$table_name} (
					term_id bigint(20) NOT NULL,
					hit bigint(20) DEFAULT 0 NOT NULL,
					UNIQUE KEY id(term_id)
				);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		return dbDelta( $sql );
	}

	# 텍스트도메인 로딩
	public static function load_plugin_textdomain() {
		$lang_dir = SJ_2DTAG_PLUGIN_NAME . '/languages';
		load_plugin_textdomain( self::$text_domain, 'wp-content/plugins/' . $lang_dir, $lang_dir );
	}

	# 플러그인 목록에 세팅 추가
	public static function action_links($links, $file) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=' . self::$text_domain . '">' . __( 'Setting', self::$text_domain ) . '</a>';
        array_unshift($links, $settings_link);

	    return $links;
	}

	# 옵션을 파싱합니다요.
	public static function get_option( $key = false ) {
		$options = get_option( 'SJ_2DTAG_CONFIG' );
		if ( $key === false ) return $options;

		$options = shortcode_atts( self::$default_option, $options[$key] );
		return $options;
	}

	# 5.0 이전 버전에서 업그레이드
	public static function upgrade_from_under_5() {
		$tag_sets = get_option('sj_tag_set');
		$options = array();

		foreach( $tag_sets as $key => $tag_set ) {
			$settings = get_option( 'sj_tag_conifg_' . $key );

			if ( $settings ) {
				foreach( $settings['tag_config']['color'] as &$color ) {
					$color['size'] = $settings['tag_config']['size'][$key];
				}

				$settings['tag_config'] = $settings['tag_config']['color'];
				unset( $settings['tag_step'] );

				$settings['title'] = $tag_set;
				$options[$key] = $settings;
			}


			delete_option( 'sj_tag_conifg_' . $key );
		}
		update_option( 'SJ_2DTAG_CONFIG', $options );
		delete_option( 'sj_tag_db_version' );
		delete_option( 'sj_tag_debug' );
		delete_option( 'sj_tag_set' );

		update_option( SJ_2DTAG_VERSION_KEY, SJ_2DTAG_VERSION_NUM );
	}

	private static function convert_css_style( $options ) {
		foreach( $options as &$color ) {
			if( !$color['bgcolor'] ) $color['bgcolor'] = 'transparent';

			$padding2 = $color['padding'] + 2;
			if ( $color['padding'] ) $color['padding'] = $color['padding'] . 'px ' . $padding2 . 'px';
		}

		return $options;
	}

	public static function increase_count() {
		global $wp_query;

		if ( !is_admin() && is_single() ) {
			self::increase_count_single( get_the_ID() );
		}

		if ( !is_admin() && is_tag() ) {
			$term = get_term_by( 'slug', $wp_query->query_vars['tag'], 'post_tag' );

			if ( $term ) {
				self::increase_count_tag( $term->term_id );
			}
		}
	}

	private static function increase_count_single( $post_id ) {
		$tags = get_the_tags( $post_id );

		# For another post type
		# 포스트 톼잎이 틀리면 get_the_tags로 가져올 수 없더라
		if ( !$tags ) {
			global $wp_query;
			$tags = get_the_terms( $post_id, 'post_tag' );
		}

		if ( $tags ) {
			foreach( $tags as $tag ) {
				self::increase_count_tag( $tag->term_id );
			}
		}
	}

	private static function increase_count_tag( $tag_id ) {
		global $wpdb;

		$table_name = self::$table_name;

		if ( $hit = $wpdb->get_var( "SELECT hit FROM {$wpdb->prefix}{$table_name} WHERE term_id = $tag_id" ) ) {
			$hit++;
			$wpdb->update( $wpdb->prefix . $table_name, array( 'hit' => $hit ), array( 'term_id' => $tag_id ) );
		} else {
			$wpdb->insert( $wpdb->prefix . $table_name, array( 'term_id' => $tag_id, 'hit' => 1 ) );
		}
	}

	private static function print_css( $set ) {
		$tag_config = self::get_option( $set );

		if ( $set === false ) {
			$set = 'default';
			$tag_config = self::$default_option;
		}

		# initialize;

		$style = 'margin-right:' . $tag_config['margin_right'] . 'px !important; ';
		$style.= 'margin-bottom:' . $tag_config['margin_bottom'] . 'px !important; ';
		$style.= 'display:inline-block !important; ';
		$style.= 'line-height:' . $tag_config['line_height'] . $tag_config['line_height_unit'] . ' !important; ';
		$style.= 'text-decoration:none !important; ';
		$style.= 'transition: all 0.5s !important; ';

		$underline = ( $tag_config['underline'] ) ? 'text-decoration:underline !important;' : '';

		$output = 'body .sj_tagcloud_set_' . $set . ' a {' . $style . '}';

		for( $i=1; $i <= count( $tag_config['tag_config'] ); $i++ ) {
			$style_color = $style_size = $style_color_over = '';

			foreach( $tag_config['tag_config'][$i] as &$css_value ) {
				if ( !$css_value ) $css_value = 'inherit';
			}

			$style_size = 'font-size:' . $tag_config['tag_config'][$i]['size'] . 'px !important; ';
			$style_color = 'color:' . $tag_config['tag_config'][$i]['color'] . ' !important; ';
			$style_color.= 'background-color:' . $tag_config['tag_config'][$i]['bgcolor'] . ' !important; ';
			$style_color.= 'border-radius:' . $tag_config['tag_config'][$i]['radius'] . 'px !important; ';
			$style_color.= 'padding:' . $tag_config['tag_config'][$i]['padding'] . 'px !important; ';

			$style_color_over = 'color:' . $tag_config['tag_config'][$i]['color_over'] . ' !important;';
			$style_color_over.= 'background-color:' . $tag_config['tag_config'][$i]['bgcolor_over'] . ' !important;';


			$output.= 'body .sj_tagcloud_set_' . $set . ' a.size_' . $i . ' {' . $style_size . '}';
			$output.= 'body .sj_tagcloud_set_' . $set . ' a.color_' . $i . ' {' . $style_color . '}';
			$output.= 'body .sj_tagcloud_set_' . $set . ' a.color_' . $i . ':hover {' . $style_color_over . ' ' . $underline . '}';
		}

		return $output;
	}

	public static function get_tag_cloud( $options ) {
		global $wpdb;
		$set = $options['set'];

		$tag_config = self::get_option( $set );

		if ( $set === false ) {
			$set = 'default';
			$tag_config = self::$default_option;
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
					LEFT JOIN ' . $wpdb->prefix . self::$table_name . ' as count ON count.term_id = terms.term_id

			WHERE
				taxonomy.taxonomy = "post_tag" AND count <> 0

			GROUP BY terms.term_id
			ORDER BY post_count DESC LIMIT ' . $options['number'] . '
		';

		$tags_count = $wpdb->get_results( $query_count ); // 포함수

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
					LEFT JOIN ' . $wpdb->prefix . self::$table_name . ' as count ON count.term_id = terms.term_id

			WHERE
				taxonomy.taxonomy = "post_tag" AND count <> 0

			GROUP BY terms.term_id
			ORDER BY post_hit DESC LIMIT ' . $options['number'] . '
		';

		$tags_hit = $wpdb->get_results($query_hit); // 히트수

		$tags = array();

		# 히트와 뷰를 한 개씩 섞는다
		$k = 0;
		for ($i=0; $i<$options['number']; $i++) {
			if (isset($tags_count[$i])) {
				$tags[$tags_count[$i]->term_id] = $tags_count[$i];
				if (!isset($tags[$tags_count[$i]->term_id])) {
					$k++;
					if ( $k == $options['number'] ) break;
				}
			}

			if ( $options['sort'] == 'intersection' ) {
				$j = $options['number'] - $i;
			} else {
				$j = $i;
			}

			if (isset($tags_hit[$j])) {
				$tags[$tags_hit[$j]->term_id] = $tags_hit[$j];
				if (!isset($tags[$tags_hit[$j]->term_id])) {
					$k++;
					if ($k == $options['number']) break;
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
		asort( $count );
		asort( $hit );

		# 한 단계에 몇 개의 태그가 들어가는지...
		$tag_step = count( $tags ) / count( $tag_config['tag_config'] );

		# 두바퀴만 더 돌려 카운트와 히트를 스텝에 맞는 값으로 변환한다
		$i = 0;
		$prev_value = -1;
		$prev_chanded = -1;

		foreach ( $count as $key => &$value ) {
			if ( $prev_value == $value ) {
				$value = $prev_chanded;
			} else {
				$prev_value = $value;
				$value = $prev_chanded = floor( $i / $tag_step ) + 1; // 0,1,2 대신 1,2,3을 사용했으니 편의상 +1
			}

			$i++;
		}

		$i = 0;
		$prev_value = -1;
		$prev_chanded = -1;

		foreach ( $hit as $key => &$value ) {
			if ( $prev_value == $value ) {
				$value = $prev_chanded;
			} else {
				$prev_value = $value;
				$value = $prev_chanded = floor( $i / $tag_step ) + 1; // 0,1,2 대신 1,2,3을 사용했으니 편의상 +1
			}

			$i++;
		}

		if ( $options['sort'] == 'name') {
			$new_tag = array();

			foreach ($tags as $tag) {
				$new_tag[strtolower( $tag->tag_name )] = $tag;
			}

			ksort( $new_tag );
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

		$output = '<div class="tag_cloud sj_tagcloud_set_' . $set . '">';
		$output.= implode( $options['separator'], $tags_out);
		$output.= '</div>';
		$output.= '<style>';
		$output.= self::print_css( $set );
		$output.= '</style>';

		return $output;
	}
}





















